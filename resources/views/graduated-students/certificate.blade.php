<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Graduation Certificate - {{ $graduate->student->student_id ?? 'Student' }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Georgia, "Times New Roman", serif;
        }

        .certificate-container {
            width: 297mm;
            height: 210mm;
            margin: auto;
            padding: 12mm;
            background: white;
        }

        .certificate {
            width: 100%;
            height: 100%;
            border: 8px double #1f4d3b;
            position: relative;
            padding: 12mm;
            text-align: center;
        }

        .certificate::before {
            content: "";
            position: absolute;
            inset: 8px;
            border: 2px solid #c9a227;
            pointer-events: none;
        }

        .school-name {
            margin-top: 8px;
            font-size: 30px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1f4d3b;
        }

        .school-subtitle {
            font-size: 15px;
            margin-top: 5px;
            color: #555;
        }

        .title {
            margin-top: 18px;
            font-size: 38px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 5px;
            color: #c9a227;
        }

        .subtitle {
            margin-top: 8px;
            font-size: 18px;
            color: #444;
        }

        .student-name {
            margin: 18px auto 8px;
            padding: 5px 30px;
            display: inline-block;
            border-bottom: 2px solid #1f4d3b;
            font-size: 32px;
            font-weight: bold;
            color: #1f4d3b;
        }

        .body-text {
            max-width: 850px;
            margin: 12px auto;
            font-size: 17px;
            line-height: 1.7;
            color: #333;
        }

        .details {
            margin: 15px auto;
            font-size: 16px;
            line-height: 1.7;
        }

        .details strong {
            color: #1f4d3b;
        }

        .signatures {
            position: absolute;
            bottom: 18mm;
            left: 20mm;
            right: 20mm;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature {
            width: 30%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 14px;
            color: #555;
        }

        .certificate-number {
            position: absolute;
            top: 15mm;
            right: 18mm;
            font-size: 12px;
            color: #666;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 18px;
            background: #1f4d3b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {
            body {
                background: white;
            }

            .certificate-container {
                margin: 0;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>

<button class="print-button" onclick="window.print()">
    Print Certificate
</button>

<div class="certificate-container">

    <div class="certificate">

        <div class="certificate-number">
            Certificate No:
            {{ $graduate->student->student_id ?? $graduate->id }}
        </div>

        {{-- School Name --}}
        <div class="school-name">
            TALHA PREMIER INTERNATIONAL ACADEMY
        </div>

        <div class="school-subtitle">
            <!-- SCHOOL MANAGEMENT INFORMATION SYSTEM -->
        </div>

        <div class="title">
            Certificate of Graduation
        </div>

        <div class="subtitle">
            This certificate is proudly presented to
        </div>

        <div class="student-name">
            {{ $graduate->student->first_name ?? '' }}
            {{ $graduate->student->middle_name ?? '' }}
            {{ $graduate->student->last_name ?? '' }}
        </div>

        <div class="body-text">
            This is to certify that the above-named student has successfully
            completed the requirements for graduation from
            <strong>
                {{ $graduate->studentClass->name ?? 'the prescribed programme' }}
            </strong>
            and has been duly recorded as a graduate of the institution.
        </div>

        <div class="details">

            <div>
                <strong>Student ID:</strong>
                {{ $graduate->student->student_id ?? 'N/A' }}
            </div>

            <div>
                <strong>Class:</strong>
                {{ $graduate->studentClass->name ?? 'N/A' }}
            </div>

            <div>
                <strong>Academic Year:</strong>
                {{ $graduate->academicYear->name ?? 'N/A' }}
            </div>

            <div>
                <strong>Graduation Status:</strong>
                Graduated
            </div>

        </div>

        <div class="body-text">
            Given in recognition of the student's successful completion
            of the prescribed academic programme.
        </div>

        <div class="signatures">

            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">
                    Head of School
                </div>
            </div>

            <!-- <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">
                    Academic Coordinator
                </div>
            </div> -->

            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">
                    Date
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>