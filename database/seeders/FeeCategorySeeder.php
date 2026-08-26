<?php

namespace Database\Seeders;

use App\Models\FeeCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tuition Fee',
                'code' => 'TUIT',
                'description' => 'Regular tuition fees for academic programs',
                'sort_order' => 1,
                'is_active' => true,
               
            ],
            [
                'name' => 'Registration Fee',
                'code' => 'REG',
                'description' => 'One-time registration fees for new students',
                'sort_order' => 2,
                'is_active' => true,
                
            ],
            [
                'name' => 'Development Fee',
                'code' => 'DEV',
                'description' => 'School infrastructure and development fees',
                'sort_order' => 3,
                'is_active' => true,
             
            ],
            [
                'name' => 'Examination Fee',
                'code' => 'EXAM',
                'description' => 'Fees for examinations and assessments',
                'sort_order' => 4,
                'is_active' => true,
             
            ],
            [
                'name' => 'Library Fee',
                'code' => 'LIB',
                'description' => 'Library and resource center fees',
                'sort_order' => 5,
                'is_active' => true,
                
            ],
            [
                'name' => 'Sports Fee',
                'code' => 'SPORT',
                'description' => 'Sports and recreational activities fees',
                'sort_order' => 6,
                'is_active' => true,
             
            ],
            [
                'name' => 'Technology Fee',
                'code' => 'TECH',
                'description' => 'Technology, ICT, and computer lab fees',
                'sort_order' => 7,
                'is_active' => true,
                
            ],
            [
                'name' => 'Science Lab Fee',
                'code' => 'SCI',
                'description' => 'Science laboratory and practical fees',
                'sort_order' => 8,
                'is_active' => true,
               
            ],
            [
                'name' => 'Medical Fee',
                'code' => 'MED',
                'description' => 'School medical and health insurance fees',
                'sort_order' => 9,
                'is_active' => true,
               
            ],
            [
                'name' => 'Transportation Fee',
                'code' => 'TRANS',
                'description' => 'School bus and transportation services',
                'sort_order' => 10,
                'is_active' => true,
               
            ],
            [
                'name' => 'Boarding Fee',
                'code' => 'BOARD',
                'description' => 'Hostel and boarding facilities fees',
                'sort_order' => 11,
                'is_active' => true,
               
            ],
            [
                'name' => 'Meal Fee',
                'code' => 'MEAL',
                'description' => 'Cafeteria and meal plan fees',
                'sort_order' => 12,
                'is_active' => true,
                
            ],
            [
                'name' => 'Activity Fee',
                'code' => 'ACT',
                'description' => 'Extracurricular activities and club fees',
                'sort_order' => 13,
                'is_active' => true,
             
            ],
            [
                'name' => 'Student Insurance',
                'code' => 'INS',
                'description' => 'Student accident and health insurance',
                'sort_order' => 14,
                'is_active' => true,
                
            ],
            [
                'name' => 'Recreation Fee',
                'code' => 'REC',
                'description' => 'Recreation and entertainment facilities',
                'sort_order' => 15,
                'is_active' => true,
                
            ],
            [
                'name' => 'Graduation Fee',
                'code' => 'GRAD',
                'description' => 'Graduation ceremony and clearance fees',
                'sort_order' => 16,
                'is_active' => true,
                
            ],
            [
                'name' => 'Late Payment Fee',
                'code' => 'LATE',
                'description' => 'Penalty for late fee payments',
                'sort_order' => 17,
                'is_active' => true,
                
            ],
            [
                'name' => 'Online Learning Fee',
                'code' => 'ONLINE',
                'description' => 'E-learning and online resources fees',
                'sort_order' => 18,
                'is_active' => true,
                
            ],
            [
                'name' => 'Student ID Card',
                'code' => 'ID',
                'description' => 'Student identification card fees',
                'sort_order' => 19,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Miscellaneous Fee',
                'code' => 'MISC',
                'description' => 'Other miscellaneous fees',
                'sort_order' => 20,
                'is_active' => true,
               
            ],
        ];

        foreach ($categories as $category) {
            // Check if category already exists
            $existing = FeeCategory::where('code', $category['code'])->first();
            
            if (!$existing) {
                FeeCategory::create([
                    'name' => $category['name'],
                    'code' => $category['code'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => $category['is_active'],
                   
                    
                ]);
            }
        }

        $this->command->info('Fee categories seeded successfully!');
    }
}