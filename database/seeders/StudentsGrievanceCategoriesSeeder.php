<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudentsGrievanceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if table exists
        if (!Schema::hasTable('students_grievance_categories')) {
            $this->command->error('❌ Table students_grievance_categories does not exist. Please run migrations first.');
            $this->command->info('Run: php artisan migrate');
            return;
        }

        $this->command->info('============================================');
        $this->command->info('🌱 Seeding Students Grievance Categories');
        $this->command->info('============================================');

        $categories = [
            [
                'name' => 'Academic Issues',
                'slug' => 'academic-issues',
                'description' => 'Issues related to academics, grades, coursework, examinations, and academic performance',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Faculty/Staff Conduct',
                'slug' => 'faculty-staff-conduct',
                'description' => 'Issues related to faculty or staff behavior, professionalism, and conduct',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bullying and Harassment',
                'slug' => 'bullying-harassment',
                'description' => 'Any form of bullying, harassment, intimidation, or victimization',
                'priority' => 'urgent',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Facilities and Infrastructure',
                'slug' => 'facilities-infrastructure',
                'description' => 'Issues with campus facilities, infrastructure, classrooms, laboratories, and amenities',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Financial Issues',
                'slug' => 'financial-issues',
                'description' => 'Issues related to tuition fees, scholarships, financial aid, payments, and billing',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student Services',
                'slug' => 'student-services',
                'description' => 'Issues with student services, counseling, career guidance, and student support',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Discrimination',
                'slug' => 'discrimination',
                'description' => 'Discrimination based on race, gender, religion, disability, sexual orientation, or any other characteristic',
                'priority' => 'urgent',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Health and Safety',
                'slug' => 'health-safety',
                'description' => 'Issues related to health, safety, security, and emergency response on campus',
                'priority' => 'urgent',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accommodation and Hostel',
                'slug' => 'accommodation-hostel',
                'description' => 'Issues related to student accommodation, hostels, dormitories, and housing facilities',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Extracurricular Activities',
                'slug' => 'extracurricular-activities',
                'description' => 'Issues related to clubs, societies, sports, and extracurricular programs',
                'priority' => 'low',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ICT and Technology',
                'slug' => 'ict-technology',
                'description' => 'Issues with ICT services, internet connectivity, computer labs, and technology resources',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Library Services',
                'slug' => 'library-services',
                'description' => 'Issues with library services, resources, facilities, and access',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student Welfare',
                'slug' => 'student-welfare',
                'description' => 'Issues related to student welfare, well-being, and pastoral care',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Curriculum and Programs',
                'slug' => 'curriculum-programs',
                'description' => 'Issues related to curriculum, course offerings, program structure, and academic programs',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Examination Issues',
                'slug' => 'examination-issues',
                'description' => 'Issues related to examinations, test scheduling, grading, and exam conduct',
                'priority' => 'high',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Attendance Issues',
                'slug' => 'attendance-issues',
                'description' => 'Issues related to attendance tracking, recording, and attendance policies',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student Leadership',
                'slug' => 'student-leadership',
                'description' => 'Issues related to student leadership, student council, and representation',
                'priority' => 'low',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peer Conflict',
                'slug' => 'peer-conflict',
                'description' => 'Conflicts and disputes between students',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transportation Issues',
                'slug' => 'transportation-issues',
                'description' => 'Issues related to student transportation, buses, and campus shuttle services',
                'priority' => 'medium',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cafeteria and Food Services',
                'slug' => 'cafeteria-food-services',
                'description' => 'Issues related to cafeteria, food quality, meal plans, and dining services',
                'priority' => 'low',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Others',
                'slug' => 'others',
                'description' => 'Any other grievances not covered in the above categories',
                'priority' => 'low',
                'is_active' => 1,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($categories as $category) {
            try {
                // Check if category already exists by slug
                $existing = DB::table('students_grievance_categories')
                    ->where('slug', $category['slug'])
                    ->first();
                
                if (!$existing) {
                    DB::table('students_grievance_categories')->insert($category);
                    $created++;
                    $this->command->info("✅ Created: {$category['name']}");
                } else {
                    $skipped++;
                    $this->command->warn("⏭ Skipped: {$category['name']} (already exists)");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("❌ Error creating {$category['name']}: " . $e->getMessage());
            }
        }

        // Display summary
        $this->command->info('============================================');
        $this->command->info('📊 Seeding Summary');
        $this->command->info('============================================');
        $this->command->info("✅ Created: {$created} categories");
        $this->command->info("⏭ Skipped: {$skipped} categories (already exist)");
        $this->command->info("❌ Errors: {$errors} categories");
        $this->command->info("📈 Total: " . count($categories) . " categories");
        $this->command->info('============================================');
        
        if ($errors === 0) {
            $this->command->info('🎉 Seeding completed successfully!');
        } else {
            $this->command->warn('⚠️ Seeding completed with errors. Please check the logs.');
        }
        $this->command->info('============================================');
    }
}