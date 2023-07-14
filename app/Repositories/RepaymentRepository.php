<?php

namespace App\Repositories;

use App\Enums\LoanStatus;
use App\Interfaces\RepaymentRepositoryInterface;
use App\Models\Repayment;

class RepaymentRepository implements RepaymentRepositoryInterface
{
    public function payForRepayment(Repayment $repayment): void
    {
        $repayment->state = LoanStatus::PAID;
        $repayment->save();

        $loan = $repayment->loan;

        // check if all approved repayment were paid
        $pendingRepaymentsCount = $loan->repayments()->where('state', LoanStatus::APPROVED)->count();
        if ($pendingRepaymentsCount === 0) {
            $loan->state = LoanStatus::PAID;
            $loan->save();
        }
    }
}
