<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Http\Requests\UpdateRepaymentRequest;
use App\Models\Repayment;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRepaymentRequest $request, Repayment $repayment)
    {
        DB::transaction(function () use ($repayment) {
            $repayment->state = LoanStatus::PAID;
            $repayment->save();

            $loan = $repayment->loan;
            $pendingRepaymentsCount = $loan->repayments()->where('state', LoanStatus::PENDING)->count();

            if ($pendingRepaymentsCount === 0) {
                $loan->state = LoanStatus::PAID;
                $loan->save();
            }
        });

        return response()->json(['message' => 'Repayment successfully paid']);
    }
}
