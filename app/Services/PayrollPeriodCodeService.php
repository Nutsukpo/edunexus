<?php

namespace App\Services;


use App\Models\PayrollPeriod;


class PayrollPeriodCodeService
{


    public function generate()
    {

        $prefix = 'PR-'.now()->format('Ym').'-';


        $last = PayrollPeriod::withTrashed()
                ->where('period_code','like',$prefix.'%')
                ->count();


        $number = str_pad(
            $last + 1,
            4,
            '0',
            STR_PAD_LEFT
        );


        return $prefix.$number;


    }


}