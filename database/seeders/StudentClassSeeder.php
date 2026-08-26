<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentClassSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Student Class Seeder...');
        $this->command->newLine();
        
        // Get the columns of student_classes table to know what fields exist
        $classColumns = Schema::getColumnListing('student_classes');
        $this->command->info('Student Classes table columns: ' . implode(', ', $classColumns));
        $this->command->newLine();
        
        // Get or create academic year 2025/2026
        $academicYear = DB::table('academic_years')->where('name', '2025/2026')->first();
        
        if (!$academicYear) {
            // Get academic_years table columns
            $yearColumns = Schema::getColumnListing('academic_years');
            
            // Prepare data based on available columns
            $yearData = [
                'name' => '2025/2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Only add is_current if the column exists
            if (in_array('is_current', $yearColumns)) {
                $yearData['is_current'] = true;
            }
            
            // Only add is_active if the column exists
            if (in_array('is_active', $yearColumns)) {
                $yearData['is_active'] = true;
            }
            
            // Insert and get ID
            $academicYearId = DB::table('academic_years')->insertGetId($yearData);
            $this->command->info("✅ Created academic year 2025/2026 (ID: {$academicYearId})");
        } else {
            $academicYearId = $academicYear->id;
            $this->command->info("✅ Using existing academic year 2025/2026 (ID: {$academicYearId})");
            
            // Update status to active (if status column exists)
            $yearColumns = Schema::getColumnListing('academic_years');
            $updateData = ['updated_at' => now()];
            
            if (in_array('status', $yearColumns)) {
                $updateData['status'] = 'active';
            }
            
            DB::table('academic_years')
                ->where('id', $academicYearId)
                ->update($updateData);
                
            $this->command->info("✅ Updated academic year status to active");
        }
        
        $this->command->newLine();
        
        // Define all classes - Primary 1-6 with streams A and B
        $classes = [];
        for ($i = 1; $i <= 6; $i++) {
            $classes[] = "Primary {$i}A";
            $classes[] = "Primary {$i}B";
        }
        
        // JHS 1-3 with streams A and B
        for ($i = 1; $i <= 3; $i++) {
            $classes[] = "JHS {$i}A";
            $classes[] = "JHS {$i}B";
        }
        
        $this->command->info('Classes to be processed:');
        $this->command->line(str_repeat('-', 30));
        foreach ($classes as $index => $className) {
            $this->command->line("  " . ($index + 1) . ". {$className}");
        }
        $this->command->line(str_repeat('-', 30));
        $this->command->info("Total: " . count($classes) . " classes");
        $this->command->newLine();
        
        // Ask for confirmation
        if (!$this->command->confirm('Do you want to proceed with seeding these classes?', true)) {
            $this->command->info('Seeder cancelled.');
            return;
        }
        
        $created = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($classes as $className) {
            try {
                // Determine education type based on class name
                $educationType = 'primary';
                if (strpos($className, 'JHS') !== false) {
                    $educationType = 'jhs';
                }
                
                // Determine class type based on class name
                $classType = 'primary';
                if (strpos($className, 'JHS') !== false) {
                    $classType = 'jhs';
                }
                
                // Check if class already exists
                $existing = DB::table('student_classes')
                    ->where('name', $className)
                    ->first();
                
                if ($existing) {
                    // Prepare update data
                    $updateData = [
                        'academic_year_id' => $academicYearId,
                        'education_type' => $educationType,
                        'class_type' => $classType,
                        'updated_at' => now()
                    ];
                    
                    // Add status if column exists
                    if (in_array('status', $classColumns)) {
                        $updateData['status'] = 'active';
                    }
                    
                    if (in_array('is_active', $classColumns)) {
                        $updateData['is_active'] = 1;
                    }
                    
                    // Update existing class
                    DB::table('student_classes')
                        ->where('id', $existing->id)
                        ->update($updateData);
                    
                    $updated++;
                    $this->command->warn("⚠️ Updated: {$className} to 2025/2026 (ID: {$existing->id})");
                } else {
                    // Prepare insert data with all required fields
                    $insertData = [
                        'name' => $className,
                        'student_class_code' => $this->generateCode($className),
                        'academic_year_id' => $academicYearId,
                        'education_type' => $educationType,
                        'class_type' => $classType,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Add status if column exists
                    if (in_array('status', $classColumns)) {
                        $insertData['status'] = 'active';
                    }
                    
                    if (in_array('is_active', $classColumns)) {
                        $insertData['is_active'] = 1;
                    }
                    
                    // Add class_level if column exists
                    if (in_array('class_level', $classColumns)) {
                        preg_match('/(Primary|JHS) (\d+)/', $className, $levelMatches);
                        $insertData['class_level'] = $levelMatches[0] ?? null;
                    }
                    
                    // Add stream if column exists
                    if (in_array('stream', $classColumns)) {
                        preg_match('/([A-Z])$/', $className, $streamMatches);
                        $insertData['stream'] = $streamMatches[1] ?? null;
                    }
                    
                    // Insert new class
                    $id = DB::table('student_classes')->insertGetId($insertData);
                    
                    $created++;
                    $this->command->line("✅ Created: {$className} (ID: {$id}, Code: {$this->generateCode($className)}, Type: {$classType})");
                }
                
            } catch (\Exception $e) {
                $errors[] = "Error processing {$className}: " . $e->getMessage();
                $this->command->error("❌ Failed to process {$className}");
            }
        }
        
        // Summary
        $this->command->newLine();
        $this->command->line(str_repeat('=', 60));
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->line(str_repeat('=', 60));
        $this->command->info("📚 Academic Year: 2025/2026 (ID: {$academicYearId})");
        $this->command->info("✅ Created: {$created} new classes");
        $this->command->warn("⚠️ Updated: {$updated} existing classes to 2025/2026");
        
        if (!empty($errors)) {
            $this->command->error("❌ Errors: " . count($errors));
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }
        
        // Show all classes with 2025/2026 academic year
        $this->command->newLine();
        $this->command->info('All classes with Academic Year 2025/2026:');
        $this->command->line(str_repeat('-', 50));
        
        $allClasses = DB::table('student_classes')
            ->where('academic_year_id', $academicYearId)
            ->orderBy('id')
            ->get();
            
        if ($allClasses->isEmpty()) {
            $this->command->warn('No classes found for academic year 2025/2026.');
        } else {
            foreach ($allClasses as $class) {
                $code = $class->student_class_code ?? 'N/A';
                $status = $class->status ?? 'active';
                $type = $class->class_type ?? 'N/A';
                $this->command->line("  ID: {$class->id} - {$class->name} (Code: {$code}, Type: {$type}, Status: {$status})");
            }
            $this->command->info("  Total: " . $allClasses->count() . " classes");
        }
        
        // Statistics
        $this->command->newLine();
        $this->command->info('📊 Statistics by Level:');
        $this->command->line(str_repeat('-', 30));
        
        $primaryCount = DB::table('student_classes')
            ->where('academic_year_id', $academicYearId)
            ->where('name', 'LIKE', 'Primary%')
            ->count();
            
        $jhsCount = DB::table('student_classes')
            ->where('academic_year_id', $academicYearId)
            ->where('name', 'LIKE', 'JHS%')
            ->count();
            
        $this->command->line("  Primary Classes: {$primaryCount}");
        $this->command->line("  JHS Classes: {$jhsCount}");
        $this->command->line("  Total: " . ($primaryCount + $jhsCount));
        
        $this->command->newLine();
        $this->command->info('✅ Student Class Seeder completed successfully!');
    }
    
    /**
     * Generate a unique class code
     */
    private function generateCode($className)
    {
        // Extract level and stream from class name
        preg_match('/(Primary|JHS) (\d+)([A-Z])/', $className, $matches);
        
        if (count($matches) >= 4) {
            $level = $matches[1];
            $number = $matches[2];
            $stream = $matches[3];
            
            // Generate code like: P1A-2025 or J3B-2025
            $prefix = substr($level, 0, 1);
            $year = '2025';
            
            return "{$prefix}{$number}{$stream}-{$year}";
        }
        
        // Fallback code
        return 'CLS-' . date('Y') . '-' . uniqid();
    }
}