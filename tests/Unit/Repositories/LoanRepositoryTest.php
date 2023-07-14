<?php

namespace Tests\Unit\Repositories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\User;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Tests\TestCase;

class LoanRepositoryTest extends TestCase
{
    protected $loanRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->loanRepository = new LoanRepository();
    }

    public function test_create_loan()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'amount' => 1000,
            'term' => 12,
        ];

        $loan = $this->loanRepository->createLoan($data);
        $this->assertInstanceOf(Loan::class, $loan);
        $this->assertEquals($data['amount'], $loan->amount);
        $this->assertEquals($data['term'], $loan->term);
        $this->assertEquals(LoanStatus::PENDING, $loan->state);
        $this->assertEquals($user->id, $loan->user_id);
        $this->assertNotNull($loan->submitted_date);
    }

    public function test_create_repayments_for_a_loan()
    {
        $loan = Loan::factory()->create(['state' => LoanStatus::PENDING]);

        $this->loanRepository->createRepaymentsByLoan($loan);

        $repayments = Repayment::where('loan_id', $loan->id)->get();

        $this->assertCount($loan->term, $repayments);

        $dueDate = Carbon::parse($loan->submitted_date)->addWeeks(1);
        $repaymentAmount = round($loan->amount / $loan->term, 2);

        foreach ($repayments as $repayment) {
            $this->assertEquals($loan->id, $repayment->loan_id);
            $this->assertEquals(LoanStatus::PENDING, $repayment->state);
            $this->assertEquals($repaymentAmount, $repayment->amount);
            $this->assertEquals($dueDate->format('Y-m-d'), Carbon::parse($repayment->due_date)->format('Y-m-d'));

            $dueDate->addWeeks(1); // Increment due date by 1 week for each iteration
        }
    }
}
