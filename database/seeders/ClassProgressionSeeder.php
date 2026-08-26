<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassProgressionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Class Progression Seeder...');
        $this->command->newLine();
        
        // Get all classes
        $classes = DB::table('student_classes')
            ->orderBy('name')
            ->get();
        
        if ($classes->isEmpty()) {
            $this->command->error('No classes found in the database. Please run StudentClassSeeder first.');
            return;
        }
        
        $this->command->info('Found ' . $classes->count() . ' classes in the database.');
        $this->command->newLine();
        
        // Clear existing progressions
        DB::table('class_progressions')->truncate();
        $this->command->info('Cleared existing progression data.');
        $this->command->newLine();
        
        // Group classes by level (Primary 1, Primary 2, etc.)
        $grouped = [];
        foreach ($classes as $class) {
            // Extract level (e.g., "Primary 1" from "Primary 1A")
            preg_match('/(Primary \d+|JHS \d+)/', $class->name, $matches);
            $level = $matches[0] ?? $class->name;
            
            if (!isset($grouped[$level])) {
                $grouped[$level] = [];
            }
            $grouped[$level][] = $class;
        }
        
        // Define the progression order
        $levelOrder = [
            'Primary 1',
            'Primary 2', 
            'Primary 3',
            'Primary 4',
            'Primary 5',
            'Primary 6',
            'JHS 1',
            'JHS 2',
            'JHS 3'
        ];
        
        $this->command->info('Creating progression paths...');
        $this->command->line(str_repeat('-', 40));
        
        $created = 0;
        $errors = [];
        
        // Create progressions for each level
        for ($i = 0; $i < count($levelOrder); $i++) {
            $currentLevel = $levelOrder[$i];
            $nextLevel = $levelOrder[$i + 1] ?? null;
            
            // If current level doesn't exist in grouped, skip
            if (!isset($grouped[$currentLevel])) {
                $this->command->warn("⚠️ Level '{$currentLevel}' not found - skipping");
                continue;
            }
            
            // For each class in this level (A, B, C, etc.)
            foreach ($grouped[$currentLevel] as $currentClass) {
                // Find matching stream in next level
                $stream = substr($currentClass->name, -1);
                $nextClass = null;
                
                if ($nextLevel && isset($grouped[$nextLevel])) {
                    // Find the class with the same stream in the next level
                    foreach ($grouped[$nextLevel] as $class) {
                        if (substr($class->name, -1) === $stream) {
                            $nextClass = $class;
                            break;
                        }
                    }
                }
                
                $isGraduation = ($nextLevel === null) ? 1 : 0;
                
                try {
                    // Insert progression
                    DB::table('class_progressions')->insert([
                        'from_class_id' => $currentClass->id,
                        'to_class_id' => $nextClass ? $nextClass->id : null,
                        'is_graduation' => $isGraduation,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $created++;
                    $toName = $nextClass ? $nextClass->name : 'GRADUATION';
                    $this->command->line("✅ {$currentClass->name} (ID: {$currentClass->id}) -> {$toName}");
                    
                } catch (\Exception $e) {
                    $errors[] = "Error creating progression for {$currentClass->name}: " . $e->getMessage();
                    $this->command->error("❌ Failed: {$currentClass->name}");
                }
            }
        }
        
        // Summary
        $this->command->newLine();
        $this->command->line(str_repeat('=', 60));
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->line(str_repeat('=', 60));
        $this->command->info("✅ Created: {$created} progression paths");
        
        if (!empty($errors)) {
            $this->command->error("❌ Errors: " . count($errors));
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }
        
        // Show all progressions
        $this->command->newLine();
        $this->command->info('All Progression Paths:');
        $this->command->line(str_repeat('-', 50));
        
        $progressions = DB::table('class_progressions')
            ->join('student_classes as from_class', 'class_progressions.from_class_id', '=', 'from_class.id')
            ->leftJoin('student_classes as to_class', 'class_progressions.to_class_id', '=', 'to_class.id')
            ->select(
                'class_progressions.*',
                'from_class.name as from_class_name',
                'to_class.name as to_class_name'
            )
            ->orderBy('from_class.name')
            ->get();
        
        if ($progressions->isEmpty()) {
            $this->command->warn('No progressions found.');
        } else {
            foreach ($progressions as $prog) {
                $toName = $prog->to_class_name ?? 'GRADUATION';
                $this->command->line("  {$prog->from_class_name} -> {$toName}" . ($prog->is_graduation ? ' 🎓' : ''));
            }
            $this->command->info("  Total: " . $progressions->count() . " progression paths");
        }
        
        $this->command->newLine();
        $this->command->info('✅ Class Progression Seeder completed successfully!');
    }
}