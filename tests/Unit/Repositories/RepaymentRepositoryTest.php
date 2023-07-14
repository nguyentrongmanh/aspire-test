<?php

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Repayment;
use App\Repositories\RepaymentRepository;
use Tests\TestCase;

class RepaymentRepositoryTest extends TestCase
{
    /** @var RepaymentRepository */
    private $repaymentRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repaymentRepository = new RepaymentRepository();
    }

    public function test_pay_for_repayment_updates_repayment_state_only()
    {
        // Create a loan with multiple approved repayments
        $loan = Loan::factory()->create(['state' => LoanStatus::APPROVED]);
        $repayment1 = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $loan->id,
        ]);
        $repayment2 = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $loan->id,
        ]);

        // Call the payForRepayment method for the first repayment
        $this->repaymentRepository->payForRepayment($repayment1);

        // Assert that the first repayment state is updated to PAID
        $this->assertEquals(LoanStatus::PAID, $repayment1->fresh()->state);

        // Assert that the loan state remains approved
        $this->assertEquals(LoanStatus::APPROVED, $loan->fresh()->state);
    }

    public function test_pay_for_repayment_updates_loan_state()
    {
        // Create a loan with a single approved repayment
        $loan = Loan::factory()->create(['state' => LoanStatus::APPROVED]);
        $repayment = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $loan->id,
        ]);

        // Call the payForRepayment method
        $this->repaymentRepository->payForRepayment($repayment);

        // Assert that the repayment state is updated to PAID
        $this->assertEquals(LoanStatus::PAID, $repayment->fresh()->state);

        // Assert that the loan state is also updated to PAID
        $this->assertEquals(LoanStatus::PAID, $loan->fresh()->state);
    }
}
