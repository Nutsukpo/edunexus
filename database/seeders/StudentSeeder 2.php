<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [

            [   'student_id' => 'STD-2021',
                'first_name' => 'Michael',
                'middle_name' => 'Kwame',
                'last_name' => 'Mensah',
                'gender' => 'Male',
                'date_of_birth' => Carbon::parse('2010-05-14'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Kumasi, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Kofi Mensah',
                'father_phone' => '0241111111',
                'father_email' => 'kofi.mensah@example.com',
                'father_occupation' => 'Teacher',

                'mother_name' => 'Ama Mensah',
                'mother_phone' => '0242222222',
                'mother_email' => 'ama.mensah@example.com',
                'mother_occupation' => 'Nurse',

                'guardian_name' => 'Kofi Mensah',
                'guardian_phone' => '0241111111',
                'guardian_email' => 'guardian1@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2012',
                'first_name' => 'Sarah',
                'middle_name' => 'Akosua',
                'last_name' => 'Owusu',
                'gender' => 'Female',
                'date_of_birth' => Carbon::parse('2011-02-10'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Accra, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Yaw Owusu',
                'father_phone' => '0243333333',
                'father_email' => 'yaw.owusu@example.com',
                'father_occupation' => 'Engineer',

                'mother_name' => 'Efua Owusu',
                'mother_phone' => '0244444444',
                'mother_email' => 'efua.owusu@example.com',
                'mother_occupation' => 'Trader',

                'guardian_name' => 'Yaw Owusu',
                'guardian_phone' => '0243333333',
                'guardian_email' => 'guardian2@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2013',
                'first_name' => 'Daniel',
                'middle_name' => 'Kojo',
                'last_name' => 'Asante',
                'gender' => 'Male',
                'date_of_birth' => Carbon::parse('2010-08-21'),
                'nationality' => 'Ghanaian',
                'religion' => 'Muslim',
                'address' => 'Cape Coast, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Kwesi Asante',
                'father_phone' => '0245555555',
                'father_email' => 'kwesi.asante@example.com',
                'father_occupation' => 'Farmer',

                'mother_name' => 'Abena Asante',
                'mother_phone' => '0246666666',
                'mother_email' => 'abena.asante@example.com',
                'mother_occupation' => 'Seamstress',

                'guardian_name' => 'Kwesi Asante',
                'guardian_phone' => '0245555555',
                'guardian_email' => 'guardian3@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2014',
                'first_name' => 'Grace',
                'middle_name' => 'Yaa',
                'last_name' => 'Boateng',
                'gender' => 'Female',
                'date_of_birth' => Carbon::parse('2012-01-11'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Tamale, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Kojo Boateng',
                'father_phone' => '0247777777',
                'father_email' => 'kojo.boateng@example.com',
                'father_occupation' => 'Doctor',

                'mother_name' => 'Martha Boateng',
                'mother_phone' => '0248888888',
                'mother_email' => 'martha.boateng@example.com',
                'mother_occupation' => 'Lecturer',

                'guardian_name' => 'Kojo Boateng',
                'guardian_phone' => '0247777777',
                'guardian_email' => 'guardian4@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2015',
                'first_name' => 'Joseph',
                'middle_name' => 'Yaw',
                'last_name' => 'Addo',
                'gender' => 'Male',
                'date_of_birth' => Carbon::parse('2011-03-25'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Sunyani, Ghana',
                'has_disability' => true,
                'disability_type' => 'Hearing Impairment',

                'father_name' => 'Yaw Addo',
                'father_phone' => '0201111111',
                'father_email' => 'yaw.addo@example.com',
                'father_occupation' => 'Driver',

                'mother_name' => 'Akua Addo',
                'mother_phone' => '0202222222',
                'mother_email' => 'akua.addo@example.com',
                'mother_occupation' => 'Trader',

                'guardian_name' => 'Yaw Addo',
                'guardian_phone' => '0201111111',
                'guardian_email' => 'guardian5@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2016',
                'first_name' => 'Priscilla',
                'middle_name' => 'Adwoa',
                'last_name' => 'Appiah',
                'gender' => 'Female',
                'date_of_birth' => Carbon::parse('2010-07-18'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Takoradi, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Nana Appiah',
                'father_phone' => '0203333333',
                'father_email' => 'nana.appiah@example.com',
                'father_occupation' => 'Lawyer',

                'mother_name' => 'Linda Appiah',
                'mother_phone' => '0204444444',
                'mother_email' => 'linda.appiah@example.com',
                'mother_occupation' => 'Banker',

                'guardian_name' => 'Nana Appiah',
                'guardian_phone' => '0203333333',
                'guardian_email' => 'guardian6@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2017',
                'first_name' => 'Samuel',
                'middle_name' => 'Koﬁ',
                'last_name' => 'Antwi',
                'gender' => 'Male',
                'date_of_birth' => Carbon::parse('2012-09-13'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Ho, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Richard Antwi',
                'father_phone' => '0205555555',
                'father_email' => 'richard.antwi@example.com',
                'father_occupation' => 'Police Officer',

                'mother_name' => 'Vida Antwi',
                'mother_phone' => '0206666666',
                'mother_email' => 'vida.antwi@example.com',
                'mother_occupation' => 'Teacher',

                'guardian_name' => 'Richard Antwi',
                'guardian_phone' => '0205555555',
                'guardian_email' => 'guardian7@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2018',
                'first_name' => 'Esther',
                'middle_name' => 'Afia',
                'last_name' => 'Gyasi',
                'gender' => 'Female',
                'date_of_birth' => Carbon::parse('2011-11-05'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Koforidua, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Stephen Gyasi',
                'father_phone' => '0207777777',
                'father_email' => 'stephen.gyasi@example.com',
                'father_occupation' => 'Businessman',

                'mother_name' => 'Janet Gyasi',
                'mother_phone' => '0208888888',
                'mother_email' => 'janet.gyasi@example.com',
                'mother_occupation' => 'Nurse',

                'guardian_name' => 'Stephen Gyasi',
                'guardian_phone' => '0207777777',
                'guardian_email' => 'guardian8@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2019',
                'first_name' => 'Emmanuel',
                'middle_name' => 'Kwabena',
                'last_name' => 'Darko',
                'gender' => 'Male',
                'date_of_birth' => Carbon::parse('2010-12-30'),
                'nationality' => 'Ghanaian',
                'religion' => 'Muslim',
                'address' => 'Bolgatanga, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Ibrahim Darko',
                'father_phone' => '0271111111',
                'father_email' => 'ibrahim.darko@example.com',
                'father_occupation' => 'Mechanic',

                'mother_name' => 'Aisha Darko',
                'mother_phone' => '0272222222',
                'mother_email' => 'aisha.darko@example.com',
                'mother_occupation' => 'Trader',

                'guardian_name' => 'Ibrahim Darko',
                'guardian_phone' => '0271111111',
                'guardian_email' => 'guardian9@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

            [
                'student_id' => 'STD-2020',
                'first_name' => 'Deborah',
                'middle_name' => 'Akua',
                'last_name' => 'Frimpong',
                'gender' => 'Female',
                'date_of_birth' => Carbon::parse('2011-06-09'),
                'nationality' => 'Ghanaian',
                'religion' => 'Christian',
                'address' => 'Obuasi, Ghana',
                'has_disability' => false,
                'disability_type' => null,

                'father_name' => 'Charles Frimpong',
                'father_phone' => '0273333333',
                'father_email' => 'charles.frimpong@example.com',
                'father_occupation' => 'Miner',

                'mother_name' => 'Patricia Frimpong',
                'mother_phone' => '0274444444',
                'mother_email' => 'patricia.frimpong@example.com',
                'mother_occupation' => 'Hairdresser',

                'guardian_name' => 'Charles Frimpong',
                'guardian_phone' => '0273333333',
                'guardian_email' => 'guardian10@example.com',

                'admission_date' => Carbon::parse('2024-09-01'),
                'photo' => null,
                'is_active' => true,
            ],

        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}