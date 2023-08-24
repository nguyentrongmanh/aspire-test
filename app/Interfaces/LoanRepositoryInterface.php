<?php

namespace App\Interfaces;

use App\Models\Loan;

interface LoanRepositoryInterface
{
    public function createLoan(array $data): Loan;

    public function createRepaymentsByLoan(Loan $loan): void;

    public function approveLoan(Loan $loan): void;
}
