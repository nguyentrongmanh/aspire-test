<?php

namespace App\Interfaces;

use App\Models\Repayment;

interface RepaymentRepositoryInterface
{
    public function payForRepayment(Repayment $repayment): void;
}
