<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'student_invoice_id',
        'fee_category_id',
        'description',
        'amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(StudentInvoice::class);
    }
}