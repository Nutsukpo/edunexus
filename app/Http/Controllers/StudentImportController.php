<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentImportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | IMPORT PAGE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $settings = SystemSetting::instance();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $classes = StudentClass::query()
            ->orderBy('name')
            ->get();

        $currentAcademicYearId = $settings->current_academic_year_id;
        $currentTermId = $settings->current_term_id;

        $terms = collect();

        if ($currentAcademicYearId) {
            $terms = Term::query()
                ->where('academic_year_id', $currentAcademicYearId)
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        }

        return view('students.import', compact(
            'academicYears',
            'classes',
            'terms',
            'currentAcademicYearId',
            'currentTermId'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | GET TERMS FOR SELECTED ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */
    public function getTerms(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
        ]);

        $terms = Term::query()
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where('is_active', true)
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'academic_year_id',
            ]);

        return response()->json([
            'success' => true,
            'terms' => $terms,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD CSV TEMPLATE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The columns below exactly match the actual students table.
    |
    | There is NO:
    | - phone
    | - email
    | - admission_number
    | - portal_access
    |
    |--------------------------------------------------------------------------
    */
    public function template()
    {
        $headers = [
            'student_id',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'date_of_birth',
            'nationality',
            'religion',
            'address',
            'has_disability',
            'disability_type',
            'father_name',
            'father_phone',
            'father_email',
            'father_occupation',
            'mother_name',
            'mother_phone',
            'mother_email',
            'mother_occupation',
            'guardian_name',
            'guardian_phone',
            'guardian_email',
            'admission_date',
        ];

        $example = [
            '',
            'John',
            '',
            'Mensah',
            'Male',
            '2015-04-12',
            'Ghanaian',
            'Christian',
            'Accra',
            'No',
            '',
            'Kwame Mensah',
            '0244111111',
            'father@example.com',
            'Trader',
            'Ama Mensah',
            '0244222222',
            'mother@example.com',
            'Trader',
            'Kwame Mensah',
            '0244111111',
            'guardian@example.com',
            '2023-09-05',
        ];

        return $this->csvDownload(
            'edunexus_student_import_template.csv',
            [
                $headers,
                $example,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW CSV
    |--------------------------------------------------------------------------
    */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'exists:terms,id',
            ],

            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */
        $academicYear = AcademicYear::findOrFail(
            $validated['academic_year_id']
        );

        if (!$academicYear->is_active) {
            throw ValidationException::withMessages([
                'academic_year_id' =>
                    'The selected academic year is not active.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TERM
        |--------------------------------------------------------------------------
        |
        | Always verify that the term belongs to the selected academic year.
        |--------------------------------------------------------------------------
        */
        $term = Term::query()
            ->where('id', $validated['term_id'])
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where('is_active', true)
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'term_id' =>
                    'The selected term does not belong to the selected academic year or is inactive.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CLASS
        |--------------------------------------------------------------------------
        */
        $studentClass = StudentClass::findOrFail(
            $validated['student_class_id']
        );

        /*
        |--------------------------------------------------------------------------
        | OPEN CSV
        |--------------------------------------------------------------------------
        */
        $file = $request->file('csv_file');

        $handle = fopen(
            $file->getRealPath(),
            'r'
        );

        if (!$handle) {
            throw ValidationException::withMessages([
                'csv_file' =>
                    'Unable to read the uploaded CSV file.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | READ HEADER
        |--------------------------------------------------------------------------
        */
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    'The uploaded CSV file is empty.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE HEADERS
        |--------------------------------------------------------------------------
        */
        $headers = array_map(
            function ($header) {
                $header = preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    (string) $header
                );

                return Str::of($header)
                    ->trim()
                    ->lower()
                    ->replace([' ', '-'], '_')
                    ->toString();
            },
            $headers
        );

        /*
        |--------------------------------------------------------------------------
        | REMOVE EMPTY HEADERS
        |--------------------------------------------------------------------------
        */
        $headers = array_values(
            array_filter(
                $headers,
                fn ($header) => $header !== ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | REQUIRED CSV COLUMNS
        |--------------------------------------------------------------------------
        */
        $requiredHeaders = [
            'first_name',
            'last_name',
            'gender',
        ];

        $missingHeaders = array_diff(
            $requiredHeaders,
            $headers
        );

        if (!empty($missingHeaders)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' =>
                    'The following required columns are missing: ' .
                    implode(', ', $missingHeaders),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | READ ROWS
        |--------------------------------------------------------------------------
        */
        $rows = [];

        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            /*
            |--------------------------------------------------------------------------
            | Skip blank rows
            |--------------------------------------------------------------------------
            */
            if (
                count(
                    array_filter(
                        $data,
                        fn ($value) =>
                            trim((string) $value) !== ''
                    )
                ) === 0
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Normalize number of columns
            |--------------------------------------------------------------------------
            */
            $data = array_pad(
                $data,
                count($headers),
                null
            );

            $data = array_slice(
                $data,
                0,
                count($headers)
            );

            $raw = array_combine(
                $headers,
                $data
            );

            if ($raw === false) {
                $rows[] = [
                    'row_number' => $rowNumber,
                    'data' => [],
                    'existing_student_id' => null,
                    'existing_student_name' => null,
                    'errors' => [
                        'Unable to read this CSV row.',
                    ],
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE ROW
            |--------------------------------------------------------------------------
            */
            $normalized = $this->normalizeRow($raw);

            /*
            |--------------------------------------------------------------------------
            | VALIDATE ROW
            |--------------------------------------------------------------------------
            */
            $errors = $this->validateImportRow(
                $normalized
            );

            /*
            |--------------------------------------------------------------------------
            | FIND EXISTING STUDENT
            |--------------------------------------------------------------------------
            |
            | student_id is optional.
            |
            | If supplied:
            | - it must exist
            | - the existing student will be assigned to the
            |   selected class/year/term
            |--------------------------------------------------------------------------
            */
            $existingStudent = null;

            if (
                !empty(
                    $normalized['student_id'] ?? null
                )
            ) {
                $existingStudent = Student::query()
                    ->where(
                        'student_id',
                        $normalized['student_id']
                    )
                    ->first();

                if (!$existingStudent) {
                    $errors[] =
                        'The supplied student ID does not exist in EDUNEXUS.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DUPLICATE STUDENT ID INSIDE SAME CSV
            |--------------------------------------------------------------------------
            */
            if (
                empty($errors)
                &&
                !empty(
                    $normalized['student_id'] ?? null
                )
            ) {
                $duplicateInFile = collect($rows)
                    ->contains(function ($existing) use ($normalized) {
                        return
                            !empty(
                                $existing['data']['student_id']
                                ?? null
                            )
                            &&
                            strcasecmp(
                                $existing['data']['student_id'],
                                $normalized['student_id']
                            ) === 0;
                    });

                if ($duplicateInFile) {
                    $errors[] =
                        'This student ID appears more than once in the uploaded CSV.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK EXISTING ASSIGNMENT
            |--------------------------------------------------------------------------
            */
            $existingAssignment = null;

            if ($existingStudent) {
                $existingAssignment =
                    StudentClassAssignment::query()
                        ->where(
                            'student_id',
                            $existingStudent->id
                        )
                        ->where(
                            'student_class_id',
                            $studentClass->id
                        )
                        ->where(
                            'academic_year_id',
                            $academicYear->id
                        )
                        ->where(
                            'term_id',
                            $term->id
                        )
                        ->whereIn(
                            'status',
                            [
                                'active',
                                'repeated',
                            ]
                        )
                        ->first();

                if ($existingAssignment) {
                    $errors[] =
                        'This student is already assigned to the selected class for the selected academic year and term.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | STORE PREVIEW ROW
            |--------------------------------------------------------------------------
            */
            $rows[] = [
                'row_number' => $rowNumber,

                'data' => $normalized,

                'existing_student_id' =>
                    $existingStudent?->id,

                'existing_student_name' =>
                    $existingStudent
                        ? $this->studentName($existingStudent)
                        : null,

                'errors' => $errors,
            ];
        }

        fclose($handle);

        /*
        |--------------------------------------------------------------------------
        | NO RECORDS
        |--------------------------------------------------------------------------
        */
        if (empty($rows)) {
            throw ValidationException::withMessages([
                'csv_file' =>
                    'No student records were found in the uploaded CSV.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE PREVIEW
        |--------------------------------------------------------------------------
        */
        $token = (string) Str::uuid();

        $directory = storage_path(
            'app/student-imports'
        );

        if (!File::exists($directory)) {
            File::makeDirectory(
                $directory,
                0755,
                true
            );
        }

        File::put(
            $directory . '/' . $token . '.json',
            json_encode(
                [
                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'student_class_id' =>
                        $studentClass->id,

                    'rows' =>
                        $rows,

                    'created_at' =>
                        now()->toIso8601String(),
                ],
                JSON_PRETTY_PRINT
            )
        );

        session([
            'student_import_token' => $token,
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */
        $totalRows = count($rows);

        $validRows = collect($rows)
            ->filter(
                fn ($row) =>
                    empty($row['errors'])
            )
            ->count();

        $invalidRows =
            $totalRows - $validRows;

        $existingRows = collect($rows)
            ->filter(
                fn ($row) =>
                    !empty(
                        $row['existing_student_id']
                    )
            )
            ->count();

        return view(
            'students.import',
            [
                'academicYears' =>
                    AcademicYear::orderByDesc('id')->get(),

                'classes' =>
                    StudentClass::orderBy('name')->get(),

                'terms' =>
                    Term::where(
                        'academic_year_id',
                        $academicYear->id
                    )
                        ->where('is_active', true)
                        ->orderBy('id')
                        ->get(),

                'currentAcademicYearId' =>
                    $academicYear->id,

                'currentTermId' =>
                    $term->id,

                'preview' =>
                    true,

                'previewRows' =>
                    $rows,

                'importToken' =>
                    $token,

                'academicYear' =>
                    $academicYear,

                'term' =>
                    $term,

                'studentClass' =>
                    $studentClass,

                'totalRows' =>
                    $totalRows,

                'validRows' =>
                    $validRows,

                'invalidRows' =>
                    $invalidRows,

                'existingRows' =>
                    $existingRows,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL IMPORT
    |--------------------------------------------------------------------------
    */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'import_token' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SECURE TOKEN
        |--------------------------------------------------------------------------
        */
        $token = basename(
            $validated['import_token']
        );

        if (
            !preg_match(
                '/^[a-f0-9-]{36}$/i',
                $token
            )
        ) {
            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'Invalid import session.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD PREVIEW
        |--------------------------------------------------------------------------
        */
        $path = storage_path(
            'app/student-imports/' .
            $token .
            '.json'
        );

        if (!File::exists($path)) {
            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'The import preview has expired. Please upload the CSV again.'
                );
        }

        $payload = json_decode(
            File::get($path),
            true
        );

        if (
            !is_array($payload)
            ||
            empty($payload['rows'])
        ) {
            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'The import preview is invalid. Please upload the CSV again.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | RELOAD PERIOD AND CLASS
        |--------------------------------------------------------------------------
        */
        $academicYear = AcademicYear::find(
            $payload['academic_year_id']
        );

        $term = Term::query()
            ->where(
                'id',
                $payload['term_id']
            )
            ->where(
                'academic_year_id',
                $payload['academic_year_id']
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        $studentClass = StudentClass::find(
            $payload['student_class_id']
        );

        if (
            !$academicYear
            ||
            !$academicYear->is_active
            ||
            !$term
            ||
            !$studentClass
        ) {
            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'The selected academic year, term or class is no longer valid.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY IMPORT VALID ROWS
        |--------------------------------------------------------------------------
        */
        $validRows = collect(
            $payload['rows']
        )->filter(
            fn ($row) =>
                empty($row['errors'])
        );

        if ($validRows->isEmpty()) {
            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'There are no valid student records to import.'
                );
        }

        $created = 0;
        $assignedExisting = 0;
        $skipped = 0;

        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */
        DB::beginTransaction();

        try {
            foreach ($validRows as $row) {
                $data = $row['data'];

                /*
                |--------------------------------------------------------------------------
                | FIND EXISTING STUDENT
                |--------------------------------------------------------------------------
                */
                $student = null;

                if (
                    !empty(
                        $data['student_id'] ?? null
                    )
                ) {
                    $student = Student::query()
                        ->where(
                            'student_id',
                            $data['student_id']
                        )
                        ->first();
                }

                /*
                |--------------------------------------------------------------------------
                | GRADUATED STUDENT PROTECTION
                |--------------------------------------------------------------------------
                |
                | Existing graduated students should not be imported into
                | another class through this process.
                |--------------------------------------------------------------------------
                */
                if ($student) {
                    $isGraduated =
                        StudentClassAssignment::query()
                            ->where(
                                'student_id',
                                $student->id
                            )
                            ->where(
                                'status',
                                'graduated'
                            )
                            ->exists();

                    if ($isGraduated) {
                        $skipped++;

                        continue;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE NEW STUDENT
                |--------------------------------------------------------------------------
                |
                | ONLY fields that exist in the actual students table
                | are inserted here.
                |--------------------------------------------------------------------------
                */
                if (!$student) {
                    $studentData = [
                        'student_id' =>
                            $this->generateStudentId(),

                        'first_name' =>
                            $this->nullIfEmpty(
                                $data['first_name'] ?? null
                            ),

                        'middle_name' =>
                            $this->nullIfEmpty(
                                $data['middle_name'] ?? null
                            ),

                        'last_name' =>
                            $this->nullIfEmpty(
                                $data['last_name'] ?? null
                            ),

                        'gender' =>
                            $this->normalizeGender(
                                $data['gender'] ?? null
                            ),

                        'date_of_birth' =>
                            $this->parseDate(
                                $data['date_of_birth'] ?? null
                            ),

                        'nationality' =>
                            $this->nullIfEmpty(
                                $data['nationality'] ?? null
                            ),

                        'religion' =>
                            $this->nullIfEmpty(
                                $data['religion'] ?? null
                            ),

                        'address' =>
                            $this->nullIfEmpty(
                                $data['address'] ?? null
                            ),

                        'has_disability' =>
                            $this->booleanValue(
                                $data['has_disability'] ?? null
                            ),

                        'disability_type' =>
                            $this->nullIfEmpty(
                                $data['disability_type'] ?? null
                            ),

                        'father_name' =>
                            $this->nullIfEmpty(
                                $data['father_name'] ?? null
                            ),

                        'father_phone' =>
                            $this->nullIfEmpty(
                                $data['father_phone'] ?? null
                            ),

                        'father_email' =>
                            $this->nullIfEmpty(
                                $data['father_email'] ?? null
                            ),

                        'father_occupation' =>
                            $this->nullIfEmpty(
                                $data['father_occupation'] ?? null
                            ),

                        'mother_name' =>
                            $this->nullIfEmpty(
                                $data['mother_name'] ?? null
                            ),

                        'mother_phone' =>
                            $this->nullIfEmpty(
                                $data['mother_phone'] ?? null
                            ),

                        'mother_email' =>
                            $this->nullIfEmpty(
                                $data['mother_email'] ?? null
                            ),

                        'mother_occupation' =>
                            $this->nullIfEmpty(
                                $data['mother_occupation'] ?? null
                            ),

                        'guardian_name' =>
                            $this->nullIfEmpty(
                                $data['guardian_name'] ?? null
                            ),

                        'guardian_phone' =>
                            $this->nullIfEmpty(
                                $data['guardian_phone'] ?? null
                            ),

                        'guardian_email' =>
                            $this->nullIfEmpty(
                                $data['guardian_email'] ?? null
                            ),

                        'admission_date' =>
                            $this->parseDate(
                                $data['admission_date'] ?? null
                            ),

                        'password_changed' =>
                            false,

                        'is_active' =>
                            true,
                    ];

                    $student = Student::create(
                        $studentData
                    );

                    $created++;
                } else {
                    $assignedExisting++;
                }

                /*
                |--------------------------------------------------------------------------
                | CHECK DUPLICATE ASSIGNMENT AGAIN
                |--------------------------------------------------------------------------
                |
                | The preview may have been generated some time ago.
                | Recheck immediately before inserting.
                |--------------------------------------------------------------------------
                */
                $duplicate = StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'student_class_id',
                        $studentClass->id
                    )
                    ->where(
                        'academic_year_id',
                        $academicYear->id
                    )
                    ->where(
                        'term_id',
                        $term->id
                    )
                    ->whereIn(
                        'status',
                        [
                            'active',
                            'repeated',
                        ]
                    )
                    ->exists();

                if ($duplicate) {
                    $skipped++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | RETIRE CURRENT ASSIGNMENT
                |--------------------------------------------------------------------------
                |
                | Historical records remain in the database.
                | Only the current active/repeated assignment is closed.
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->whereIn(
                        'status',
                        [
                            'active',
                            'repeated',
                        ]
                    )
                    ->update([
                        'is_current' =>
                            false,

                        'status' =>
                            'inactive',
                    ]);

                /*
                |--------------------------------------------------------------------------
                | CREATE NEW CLASS ASSIGNMENT
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::create([
                    'student_id' =>
                        $student->id,

                    'student_class_id' =>
                        $studentClass->id,

                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'status' =>
                        'active',

                    'assigned_date' =>
                        now(),

                    'is_current' =>
                        true,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */
            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | DELETE TEMPORARY PREVIEW
            |--------------------------------------------------------------------------
            */
            File::delete($path);

            session()->forget(
                'student_import_token'
            );

            /*
            |--------------------------------------------------------------------------
            | SUCCESS MESSAGE
            |--------------------------------------------------------------------------
            */
            $message =
                'Student import completed successfully. ' .
                $created .
                ' new student(s) created and ' .
                $assignedExisting .
                ' existing student(s) processed.';

            if ($skipped > 0) {
                $message .=
                    ' ' .
                    $skipped .
                    ' student(s) were skipped because they could not be assigned or the assignment already existed.';
            }

            return redirect()
                ->route('students.index')
                ->with(
                    'success',
                    $message
                );
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | ROLLBACK EVERYTHING
            |--------------------------------------------------------------------------
            */
            DB::rollBack();

            report($e);

            return redirect()
                ->route('students.import')
                ->with(
                    'error',
                    'The student import failed and no database changes were saved. Error: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE IMPORT ROW
    |--------------------------------------------------------------------------
    */
    private function validateImportRow(
        array $row
    ): array {
        $errors = [];

        /*
        |--------------------------------------------------------------------------
        | FIRST NAME
        |--------------------------------------------------------------------------
        */
        if (
            empty(
                trim(
                    (string)
                    ($row['first_name'] ?? '')
                )
            )
        ) {
            $errors[] =
                'First name is required.';
        }

        /*
        |--------------------------------------------------------------------------
        | LAST NAME
        |--------------------------------------------------------------------------
        */
        if (
            empty(
                trim(
                    (string)
                    ($row['last_name'] ?? '')
                )
            )
        ) {
            $errors[] =
                'Last name is required.';
        }

        /*
        |--------------------------------------------------------------------------
        | GENDER
        |--------------------------------------------------------------------------
        */
        if (
            empty(
                trim(
                    (string)
                    ($row['gender'] ?? '')
                )
            )
        ) {
            $errors[] =
                'Gender is required.';
        } elseif (
            !in_array(
                strtolower(
                    trim(
                        (string)
                        $row['gender']
                    )
                ),
                [
                    'male',
                    'female',
                ],
                true
            )
        ) {
            $errors[] =
                'Gender must be Male or Female.';
        }

        /*
        |--------------------------------------------------------------------------
        | DATE OF BIRTH
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $row['date_of_birth'] ?? null
            )
            &&
            !$this->validDate(
                $row['date_of_birth']
            )
        ) {
            $errors[] =
                'Invalid date of birth.';
        }

        /*
        |--------------------------------------------------------------------------
        | ADMISSION DATE
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $row['admission_date'] ?? null
            )
            &&
            !$this->validDate(
                $row['admission_date']
            )
        ) {
            $errors[] =
                'Invalid admission date.';
        }

        /*
        |--------------------------------------------------------------------------
        | EMAIL VALIDATION
        |--------------------------------------------------------------------------
        |
        | These are actual columns:
        | father_email
        | mother_email
        | guardian_email
        |--------------------------------------------------------------------------
        */
        foreach (
            [
                'father_email',
                'mother_email',
                'guardian_email',
            ] as $emailField
        ) {
            if (
                !empty(
                    $row[$emailField] ?? null
                )
                &&
                !filter_var(
                    $row[$emailField],
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $errors[] =
                    'Invalid ' .
                    str_replace(
                        '_',
                        ' ',
                        $emailField
                    ) .
                    '.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DISABILITY VALUE
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $row['has_disability'] ?? null
            )
        ) {
            $value = strtolower(
                trim(
                    (string)
                    $row['has_disability']
                )
            );

            if (
                !in_array(
                    $value,
                    [
                        'yes',
                        'no',
                        '1',
                        '0',
                        'true',
                        'false',
                        'y',
                        'n',
                    ],
                    true
                )
            ) {
                $errors[] =
                    'Has disability must be Yes or No.';
            }
        }

        return $errors;
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE CSV ROW
    |--------------------------------------------------------------------------
    */
    private function normalizeRow(
        array $row
    ): array {
        $result = [];

        foreach ($row as $key => $value) {
            $key = Str::of($key)
                ->trim()
                ->lower()
                ->replace(
                    [' ', '-'],
                    '_'
                )
                ->toString();

            $result[$key] = trim(
                (string)
                ($value ?? '')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CSV ALIASES
        |--------------------------------------------------------------------------
        */
        $aliases = [
            'firstname' =>
                'first_name',

            'first' =>
                'first_name',

            'middlename' =>
                'middle_name',

            'middle' =>
                'middle_name',

            'lastname' =>
                'last_name',

            'last' =>
                'last_name',

            'dob' =>
                'date_of_birth',

            'dateofbirth' =>
                'date_of_birth',

            'admission' =>
                'admission_date',

            'date_admitted' =>
                'admission_date',

            'studentid' =>
                'student_id',

            'student_number' =>
                'student_id',

            'disability' =>
                'has_disability',

            'fatherphone' =>
                'father_phone',

            'fatheremail' =>
                'father_email',

            'motherphone' =>
                'mother_phone',

            'motheremail' =>
                'mother_email',

            'guardianphone' =>
                'guardian_phone',

            'guardianemail' =>
                'guardian_email',
        ];

        foreach (
            $aliases as $from => $to
        ) {
            if (
                isset($result[$from])
                &&
                !isset($result[$to])
            ) {
                $result[$to] =
                    $result[$from];
            }
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE GENDER
    |--------------------------------------------------------------------------
    */
    private function normalizeGender(
        ?string $gender
    ): ?string {
        if (!$gender) {
            return null;
        }

        $gender = strtolower(
            trim($gender)
        );

        if ($gender === 'male') {
            return 'Male';
        }

        if ($gender === 'female') {
            return 'Female';
        }

        return ucfirst($gender);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE PERMANENT STUDENT ID
    |--------------------------------------------------------------------------
    |
    | New EDUNEXUS IDs use:
    |
    | STD-000001
    | STD-000002
    | STD-000003
    |
    | Existing legacy IDs are never changed.
    |--------------------------------------------------------------------------
    */
    private function generateStudentId(): string
    {
        $number = 1;

        do {
            $studentId =
                'STD-' .
                str_pad(
                    $number,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $exists = Student::query()
                ->where(
                    'student_id',
                    $studentId
                )
                ->exists();

            $number++;
        } while ($exists);

        return $studentId;
    }

    /*
    |--------------------------------------------------------------------------
    | DATE VALIDATION
    |--------------------------------------------------------------------------
    */
    private function validDate(
        ?string $value
    ): bool {
        if (!$value) {
            return true;
        }

        try {
            Carbon::parse($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DATE PARSER
    |--------------------------------------------------------------------------
    */
    private function parseDate(
        ?string $value
    ): ?string {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BOOLEAN VALUE
    |--------------------------------------------------------------------------
    */
    private function booleanValue(
        $value
    ): bool {
        $value = strtolower(
            trim(
                (string)
                ($value ?? '')
            )
        );

        return in_array(
            $value,
            [
                '1',
                'true',
                'yes',
                'y',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NULL EMPTY VALUES
    |--------------------------------------------------------------------------
    */
    private function nullIfEmpty(
        $value
    ) {
        $value = trim(
            (string)
            ($value ?? '')
        );

        return $value === ''
            ? null
            : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT NAME
    |--------------------------------------------------------------------------
    */
    private function studentName(
        Student $student
    ): string {
        return trim(
            implode(
                ' ',
                array_filter([
                    $student->first_name,
                    $student->middle_name,
                    $student->last_name,
                ])
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CSV DOWNLOAD
    |--------------------------------------------------------------------------
    */
    private function csvDownload(
        string $filename,
        array $rows
    ) {
        return response()->streamDownload(
            function () use ($rows) {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                |--------------------------------------------------------------------------
                | UTF-8 BOM
                |--------------------------------------------------------------------------
                |
                | Helps Microsoft Excel recognize UTF-8 CSV files.
                |--------------------------------------------------------------------------
                */
                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                foreach ($rows as $row) {
                    fputcsv(
                        $handle,
                        $row
                    );
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }
}