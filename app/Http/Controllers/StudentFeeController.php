<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Legacy compatibility controller.
 *
 * The student portal now uses StudentFeesController as the authoritative
 * fee controller. This class remains so older routes pointing to
 * StudentFeeController do not break.
 */
class StudentFeeController extends StudentFeesController
{
    /**
     * Legacy alias for the student fee dashboard.
     */
    public function show($id = null)
    {
        return $this->index();
    }

    /**
     * Legacy payment endpoint.
     *
     * The old controller used StudentFeeAllocation/Payment. Those are no
     * longer the authoritative student-portal financial records. Payment
     * processing now goes through BillSheet + FeePayment and the admin's
     * operational academic period.
     */
    public function makePayment(Request $request, $id = null)
    {
        return $this->initiatePayment($request);
    }

    /**
     * Legacy payment-history endpoint.
     */
    public function paymentHistory()
    {
        return redirect()->route('students.fees');
    }

    /**
     * Legacy receipt endpoint.
     */
    public function downloadReceipt($id)
    {
        return $this->receiptPdf($id);
    }
}
