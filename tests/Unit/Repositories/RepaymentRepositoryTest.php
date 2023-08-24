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
        $this->loan = Loan::factory()->create(['state' => LoanStatus::APPROVED]);
    }

    public function test_pay_for_repayment_updates_repayment_state_only()
    {
        // Create multiple approved repayments
        
        $repayment1 = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $this->loan->id,
        ]);
        $repayment2 = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $this->loan->id,
        ]);

        // Call the payForRepayment method for the first repayment
        $this->repaymentRepository->payForRepayment($repayment1);

        // Assert that the first repayment state is updated to PAID
        $this->assertEquals(LoanStatus::PAID, $repayment1->fresh()->state);

        // Assert that the loan state remains approved
        $this->assertEquals(LoanStatus::APPROVED, $this->loan->fresh()->state);
    }

    public function test_pay_for_repayment_updates_loan_state()
    {
        // Create a single approved repayment
        $repayment = Repayment::factory()->create([
            'state' => LoanStatus::APPROVED,
            'loan_id' => $this->loan->id,
        ]);

        // Call the payForRepayment method
        $this->repaymentRepository->payForRepayment($repayment);

        // Assert that the repayment state is updated to PAID
        $this->assertEquals(LoanStatus::PAID, $repayment->fresh()->state);

        // Assert that the loan state is also updated to PAID
        $this->assertEquals(LoanStatus::PAID, $this->loan->fresh()->state);
    }
}
