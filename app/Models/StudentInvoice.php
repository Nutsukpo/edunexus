<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentInvoice extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'term_id',
        'student_class_id',
        'invoice_number',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
   
}