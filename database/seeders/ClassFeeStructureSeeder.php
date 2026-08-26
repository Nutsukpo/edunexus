<?php

namespace Database\Seeders;

use App\Models\ClassFeeStructure;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassFeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Start transaction for data integrity
        DB::beginTransaction();

        try {
            // Get the current academic year or create one if none exists
            $academicYear = AcademicYear::where('is_active', true)->first();
            
            if (!$academicYear) {
                // Create a default academic year if none exists
                $academicYear = AcademicYear::create([
                    'name' => date('Y') . '/' . (date('Y') + 1),
                    'start_date' => date('Y-09-01'),
                    'end_date' => (date('Y') + 1) . '-08-31',
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                ]);
            }

            // Get all active classes or fallback to all classes
            $classes = StudentClass::where('is_active', true)->get();
            
            if ($classes->isEmpty()) {
                $classes = StudentClass::all();
            }

            // Get admin user for created_by
            $adminUser = User::where('email', 'admin@example.com')->first();
            $createdBy = $adminUser ? $adminUser->id : 1;

            // Define fee structures with more comprehensive details
            $feeTypes = $this->getFeeTypes();

            // For each class, create fee structures
            foreach ($classes as $class) {
                echo "Creating fee structures for class: {$class->name}\n";
                
                foreach ($feeTypes as $feeType) {
                    // Calculate amount based on class level
                    $amount = $this->calculateAmountByClass($feeType['base_amount'], $class);
                    
                    // Set due date based on fee type
                    $dueDate = $this->getDueDateForFeeType($feeType['fee_type'], $academicYear);

                    // Check if fee structure already exists to avoid duplicates
                    $existing = ClassFeeStructure::where([
                        'student_class_id' => $class->id,
                        'academic_year_id' => $academicYear->id,
                        'fee_type' => $feeType['fee_type'],
                    ])->first();

                    if (!$existing) {
                        ClassFeeStructure::create([
                            'student_class_id' => $class->id,
                            'academic_year_id' => $academicYear->id,
                            'fee_type' => $feeType['fee_type'],
                            'fee_name' => $feeType['fee_name'],
                            'amount' => round($amount, 2),
                            'description' => $this->getFeeDescription($feeType, $class),
                            'is_required' => $feeType['is_required'] ?? true,
                            'is_active' => true,
                            'due_date' => $dueDate,
                            'created_by' => $createdBy,
                            'metadata' => [
                                'class_level' => $this->getClassLevel($class),
                                'fee_category' => $feeType['category'] ?? 'general',
                            ],
                        ]);
                        
                        echo "  - Created: {$feeType['fee_name']} - GHS " . number_format($amount, 2) . "\n";
                    } else {
                        echo "  - Skipped: {$feeType['fee_name']} (already exists)\n";
                    }
                }
            }

            DB::commit();
            echo "Class fee structures seeded successfully!\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error seeding class fee structures: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Get all fee types with their details
     */
    private function getFeeTypes(): array
    {
        return [
            [
                'fee_type' => 'tuition',
                'fee_name' => 'Tuition Fee',
                'base_amount' => 1500.00,
                'is_required' => true,
                'category' => 'academic',
                'description' => 'Basic tuition fee for the academic year',
            ],
            [
                'fee_type' => 'registration',
                'fee_name' => 'Registration Fee',
                'base_amount' => 200.00,
                'is_required' => true,
                'category' => 'administrative',
                'description' => 'Annual registration and enrollment fee',
            ],
            [
                'fee_type' => 'development',
                'fee_name' => 'Development Fee',
                'base_amount' => 300.00,
                'is_required' => true,
                'category' => 'infrastructure',
                'description' => 'School development and infrastructure maintenance fee',
            ],
            [
                'fee_type' => 'library',
                'fee_name' => 'Library Fee',
                'base_amount' => 100.00,
                'is_required' => false,
                'category' => 'resources',
                'description' => 'Library resources, books, and materials fee',
            ],
            [
                'fee_type' => 'sports',
                'fee_name' => 'Sports Fee',
                'base_amount' => 150.00,
                'is_required' => false,
                'category' => 'activities',
                'description' => 'Sports and athletics activities fee',
            ],
            [
                'fee_type' => 'insurance',
                'fee_name' => 'Insurance Fee',
                'base_amount' => 50.00,
                'is_required' => false,
                'category' => 'health',
                'description' => 'Student health insurance coverage',
            ],
            [
                'fee_type' => 'science_lab',
                'fee_name' => 'Science Laboratory Fee',
                'base_amount' => 250.00,
                'is_required' => false,
                'category' => 'academic',
                'description' => 'Science laboratory equipment and materials',
            ],
            [
                'fee_type' => 'computer_lab',
                'fee_name' => 'Computer Lab Fee',
                'base_amount' => 200.00,
                'is_required' => false,
                'category' => 'technology',
                'description' => 'Computer lab access and IT resources',
            ],
            [
                'fee_type' => 'transport',
                'fee_name' => 'Transport Fee',
                'base_amount' => 400.00,
                'is_required' => false,
                'category' => 'services',
                'description' => 'School transport services (optional)',
            ],
            [
                'fee_type' => 'boarding',
                'fee_name' => 'Boarding Fee',
                'base_amount' => 2000.00,
                'is_required' => false,
                'category' => 'services',
                'description' => 'Boarding and accommodation fee (optional)',
            ],
            [
                'fee_type' => 'examination',
                'fee_name' => 'Examination Fee',
                'base_amount' => 180.00,
                'is_required' => true,
                'category' => 'academic',
                'description' => 'Terminal and promotional examination fee',
            ],
            [
                'fee_type' => 'pta',
                'fee_name' => 'PTA Levy',
                'base_amount' => 80.00,
                'is_required' => true,
                'category' => 'administrative',
                'description' => 'Parent-Teacher Association annual levy',
            ],
        ];
    }

    /**
     * Calculate fee amount based on class level
     */
    private function calculateAmountByClass(float $baseAmount, StudentClass $class): float
    {
        $classLevel = $this->getClassLevel($class);
        $multiplier = 1.0;

        // Adjust fees based on class level
        switch ($classLevel) {
            case 'kindergarten':
                $multiplier = 0.7;
                break;
            case 'lower_primary':
                $multiplier = 0.8;
                break;
            case 'upper_primary':
                $multiplier = 0.9;
                break;
            case 'junior_high':
                $multiplier = 1.1;
                break;
            case 'senior_high':
                $multiplier = 1.3;
                break;
            case 'tertiary':
                $multiplier = 1.5;
                break;
        }

        // Additional adjustment for boarding or specialized classes
        if (strpos(strtolower($class->name), 'boarding') !== false) {
            $multiplier *= 1.2;
        }

        if (strpos(strtolower($class->name), 'science') !== false) {
            $multiplier *= 1.1;
        }

        return $baseAmount * $multiplier;
    }

    /**
     * Get class level from class name or type
     */
    private function getClassLevel(StudentClass $class): string
    {
        $name = strtolower($class->name);
        
        if (strpos($name, 'kg') !== false || strpos($name, 'kindergarten') !== false) {
            return 'kindergarten';
        }
        if (strpos($name, 'primary 1') !== false || strpos($name, 'primary 2') !== false || 
            strpos($name, 'primary 3') !== false || strpos($name, 'p.1') !== false || 
            strpos($name, 'p.2') !== false || strpos($name, 'p.3') !== false) {
            return 'lower_primary';
        }
        if (strpos($name, 'primary 4') !== false || strpos($name, 'primary 5') !== false || 
            strpos($name, 'primary 6') !== false || strpos($name, 'p.4') !== false || 
            strpos($name, 'p.5') !== false || strpos($name, 'p.6') !== false) {
            return 'upper_primary';
        }
        if (strpos($name, 'jhs') !== false || strpos($name, 'junior high') !== false || 
            strpos($name, 'junior secondary') !== false) {
            return 'junior_high';
        }
        if (strpos($name, 'shs') !== false || strpos($name, 'senior high') !== false || 
            strpos($name, 'senior secondary') !== false) {
            return 'senior_high';
        }
        if (strpos($name, 'university') !== false || strpos($name, 'college') !== false) {
            return 'tertiary';
        }
        
        return 'general';
    }

    /**
     * Get due date based on fee type
     */
    private function getDueDateForFeeType(string $feeType, AcademicYear $academicYear): ?string
    {
        $startDate = $academicYear->start_date ? new \DateTime($academicYear->start_date) : new \DateTime();
        
        switch ($feeType) {
            case 'registration':
                return $startDate->modify('+1 month')->format('Y-m-d');
            case 'tuition':
                return $startDate->modify('+2 months')->format('Y-m-d');
            case 'examination':
                return $startDate->modify('+6 months')->format('Y-m-d');
            case 'boarding':
                return $startDate->modify('+3 months')->format('Y-m-d');
            default:
                return $startDate->modify('+3 months')->format('Y-m-d');
        }
    }

    /**
     * Get detailed fee description
     */
    private function getFeeDescription(array $feeType, StudentClass $class): string
    {
        $classLevel = $this->getClassLevel($class);
        $levelNames = [
            'kindergarten' => 'Kindergarten',
            'lower_primary' => 'Lower Primary',
            'upper_primary' => 'Upper Primary',
            'junior_high' => 'Junior High School',
            'senior_high' => 'Senior High School',
            'tertiary' => 'Tertiary',
            'general' => $class->name,
        ];

        $levelName = $levelNames[$classLevel] ?? $class->name;
        
        return sprintf(
            '%s for %s class (%s) - %s',
            $feeType['description'],
            $levelName,
            $class->student_class_code ?? $class->id,
            date('Y')
        );
    }
}