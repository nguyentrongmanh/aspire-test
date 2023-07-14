<?php

namespace App\Repositories;

use App\Enums\LoanStatus;
use App\Interfaces\LoanRepositoryInterface;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LoanRepository implements LoanRepositoryInterface
{
    public function createLoan(array $data): Loan
    {
        $loan = new Loan();
        $loan->amount = $data['amount'];
        $loan->term = $data['term'];
        $loan->state = LoanStatus::PENDING;
        $loan->user_id = Auth::user()->id;
        $loan->submitted_date = Carbon::now()->format('Y-m-d');
        $loan->save();

        return $loan;
    }

    public function createRepaymentsByLoan(Loan $loan): void
    {
        $repayments = [];
        $repaymentAmount = round($loan->amount / $loan->term, 2);

        for ($i = 0; $i < $loan->term; $i++) {
            $dueDate = Carbon::parse($loan->submitted_date)->addWeeks($i + 1);

            $repayments[] = [
                'loan_id' => $loan->id,
                'due_date' => $dueDate,
                'amount' => $repaymentAmount,
                'state' => LoanStatus::PENDING,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Repayment::insert($repayments);
    }
}
