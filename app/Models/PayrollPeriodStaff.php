<?php
// app/Models/PayrollPeriodStaff.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriodStaff extends Model
{
    protected $table = 'payroll_period_staff';

    protected $fillable = [
        'payroll_period_id',
        'staff_id',
        'basic_salary',
        'allowances',
        'deductions',
        'gross_salary',
        'total_deduction',
        'net_salary',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the payroll period.
     */
    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the staff.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['Draft', 'Processing']);
    }

    use Barryvdh\DomPDF\Facade\Pdf;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    public function exportExcel(PayrollPeriod $payrollPeriod)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Staff Name');
        $sheet->setCellValue('B1', 'Department');
        $sheet->setCellValue('C1', 'Position');
        $sheet->setCellValue('D1', 'Gross Salary');
        $sheet->setCellValue('E1', 'Deductions');
        $sheet->setCellValue('F1', 'Net Salary');
        
        // Add data
        $row = 2;
        foreach ($payrollPeriod->staff as $staff) {
            $sheet->setCellValue('A' . $row, $staff->first_name . ' ' . $staff->last_name);
            $sheet->setCellValue('B' . $row, $staff->department ?? 'N/A');
            $sheet->setCellValue('C' . $row, $staff->position ?? 'N/A');
            $sheet->setCellValue('D' . $row, $staff->pivot->gross_salary ?? 0);
            $sheet->setCellValue('E' . $row, $staff->pivot->total_deduction ?? 0);
            $sheet->setCellValue('F' . $row, $staff->pivot->net_salary ?? 0);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'payroll_' . $payrollPeriod->period_code . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }
    
    public function exportPdf(PayrollPeriod $payrollPeriod)
    {
        $data = [
            'payrollPeriod' => $payrollPeriod,
            'staff' => $payrollPeriod->staff
        ];
        
        $pdf = Pdf::loadView('exports.payroll-period-pdf', $data);
        return $pdf->download('payroll_' . $payrollPeriod->period_code . '.pdf');
    }
    
    public function exportWord(PayrollPeriod $payrollPeriod)
    {
        $data = [
            'payrollPeriod' => $payrollPeriod,
            'staff' => $payrollPeriod->staff
        ];
        
        $html = view('exports.payroll-period-word', $data)->render();
        
        $filename = 'payroll_' . $payrollPeriod->period_code . '.doc';
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        echo $html;
        exit;
    }

        /**
     * Export payroll period to Excel
     */
    public function exportExcel(PayrollPeriod $payrollPeriod)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'S/N');
            $sheet->setCellValue('B1', 'Staff Name');
            $sheet->setCellValue('C1', 'Staff Code');
            $sheet->setCellValue('D1', 'Department');
            $sheet->setCellValue('E1', 'Position');
            $sheet->setCellValue('F1', 'Gross Salary');
            $sheet->setCellValue('G1', 'Deductions');
            $sheet->setCellValue('H1', 'Net Salary');

            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDCB6E']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);

            // Add period info
            $sheet->setCellValue('A' . ($row = 1), 'Payroll Period: ' . $payrollPeriod->name);
            $sheet->setCellValue('A' . ($row + 1), 'Period Code: ' . $payrollPeriod->period_code);
            $sheet->setCellValue('A' . ($row + 2), 'Month: ' . $payrollPeriod->month . ' - ' . $payrollPeriod->year);
            $sheet->setCellValue('A' . ($row + 3), 'Status: ' . $payrollPeriod->status);
            
            $startRow = 6;

            // Add data
            $row = $startRow;
            $sn = 1;
            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;

            foreach ($payrollPeriod->staff as $staff) {
                $grossSalary = $staff->pivot->gross_salary ?? 0;
                $deductions = $staff->pivot->total_deduction ?? 0;
                $netSalary = $staff->pivot->net_salary ?? 0;

                $sheet->setCellValue('A' . $row, $sn++);
                $sheet->setCellValue('B' . $row, $staff->first_name . ' ' . $staff->last_name);
                $sheet->setCellValue('C' . $row, $staff->staff_code ?? 'N/A');
                $sheet->setCellValue('D' . $row, $staff->department ?? 'N/A');
                $sheet->setCellValue('E' . $row, $staff->position ?? 'N/A');
                $sheet->setCellValue('F' . $row, number_format($grossSalary, 2));
                $sheet->setCellValue('G' . $row, number_format($deductions, 2));
                $sheet->setCellValue('H' . $row, number_format($netSalary, 2));

                $totalGross += $grossSalary;
                $totalDeductions += $deductions;
                $totalNet += $netSalary;
                $row++;
            }

            // Add totals row
            if ($payrollPeriod->staff->isNotEmpty()) {
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, '');
                $sheet->setCellValue('C' . $row, '');
                $sheet->setCellValue('D' . $row, '');
                $sheet->setCellValue('E' . $row, 'TOTAL');
                $sheet->setCellValue('F' . $row, number_format($totalGross, 2));
                $sheet->setCellValue('G' . $row, number_format($totalDeductions, 2));
                $sheet->setCellValue('H' . $row, number_format($totalNet, 2));
                
                $totalStyle = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DFE6E9']]
                ];
                $sheet->getStyle('E' . $row . ':H' . $row)->applyFromArray($totalStyle);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'payroll_' . $payrollPeriod->period_code . '_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export payroll period to PDF
     */
    public function exportPdf(PayrollPeriod $payrollPeriod)
    {
        try {
            $data = [
                'payrollPeriod' => $payrollPeriod,
                'staff' => $payrollPeriod->staff,
                'totalGross' => $payrollPeriod->staff->sum('pivot.gross_salary'),
                'totalDeductions' => $payrollPeriod->staff->sum('pivot.total_deduction'),
                'totalNet' => $payrollPeriod->staff->sum('pivot.net_salary')
            ];

            $pdf = Pdf::loadView('exports.payroll-period-pdf', $data);
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('payroll_' . $payrollPeriod->period_code . '_' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export payroll period to Word
     */
    public function exportWord(PayrollPeriod $payrollPeriod)
    {
        try {
            $data = [
                'payrollPeriod' => $payrollPeriod,
                'staff' => $payrollPeriod->staff,
                'totalGross' => $payrollPeriod->staff->sum('pivot.gross_salary'),
                'totalDeductions' => $payrollPeriod->staff->sum('pivot.total_deduction'),
                'totalNet' => $payrollPeriod->staff->sum('pivot.net_salary')
            ];
            
            $html = view('exports.payroll-period-word', $data)->render();
            
            $filename = 'payroll_' . $payrollPeriod->period_code . '_' . date('Y-m-d') . '.doc';
            header('Content-Type: application/msword');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            echo $html;
            exit;
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting Word: ' . $e->getMessage());
        }
    }

    /**
     * Export all formats (zip)
     */
    public function exportAll(PayrollPeriod $payrollPeriod)
    {
        try {
            // Create a temporary directory
            $tempDir = storage_path('app/temp/' . uniqid());
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Export Excel
            $this->exportExcelFile($payrollPeriod, $tempDir . '/payroll.xlsx');
            
            // Export PDF
            $pdf = Pdf::loadView('exports.payroll-period-pdf', [
                'payrollPeriod' => $payrollPeriod,
                'staff' => $payrollPeriod->staff
            ]);
            $pdf->save($tempDir . '/payroll.pdf');

            // Export Word
            $html = view('exports.payroll-period-word', [
                'payrollPeriod' => $payrollPeriod,
                'staff' => $payrollPeriod->staff
            ])->render();
            file_put_contents($tempDir . '/payroll.doc', $html);

            // Create zip
            $zip = new \ZipArchive();
            $zipFileName = storage_path('app/temp/payroll_' . $payrollPeriod->period_code . '.zip');
            if ($zip->open($zipFileName, \ZipArchive::CREATE) === TRUE) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempDir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($tempDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
            }

            // Clean up
            $this->deleteDirectory($tempDir);

            return response()->download($zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting all formats: ' . $e->getMessage());
        }
    }

    private function exportExcelFile(PayrollPeriod $payrollPeriod, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Same as exportExcel method but save to file
        // ... (copy the Excel generation code here)
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}
