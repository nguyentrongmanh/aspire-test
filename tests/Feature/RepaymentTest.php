<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Interfaces\RepaymentRepositoryInterface;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class RepaymentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->loan = Loan::factory()->create(['user_id' => $this->user->id, 'state' => LoanStatus::APPROVED]);
        $this->actingAs($this->user);
    }

    /**
     * Test user can pay for a repayment
     *
     * @return void
     */
    public function test_user_can_pay_for_repayment()
    {
        $repayments = Repayment::factory()->count($this->loan->term)->create([
            'loan_id' => $this->loan->id,
            'state' => LoanStatus::APPROVED,
        ]);

        foreach ($repayments as &$repayment) {
            $paymentAmount = $repayment->amount;
            $this->put(route('repayments.update', ['repayment' => $repayment]), [
                'amount' => $paymentAmount,
            ])->assertStatus(Response::HTTP_OK);
            $repayment->refresh();
            $this->assertEquals(LoanStatus::PAID, $repayment->state);
        }

        $this->loan->refresh();

        $this->assertEquals(LoanStatus::PAID, $this->loan->state);
    }

    /**
     * Test user can not pay with insufficient payment amount
     *
     * @return void
     */
    public function test_user_cant_pay_with_insufficient_payment_amount()
    {
        $repayment = Repayment::factory()->create([
            'loan_id' => $this->loan->id,
            'state' => LoanStatus::APPROVED,
        ]);

        $paymentAmount = $repayment->amount - 1;
        $this->put(route('repayments.update', ['repayment' => $repayment]), [
            'amount' => $paymentAmount,
        ])->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrorFor('amount');
    }

    /**
     * Test user can not pay for an unapproved loan
     *
     * @return void
     */
    public function test_user_cant_pay_for_unapproved_loan()
    {
        $repayment = Repayment::factory()->create([
            'loan_id' => $this->loan->id,
            'state' => LoanStatus::PENDING,
        ]);

        $response = $this->put(route('repayments.update', ['repayment' => $repayment]), [
            'amount' => $repayment->amount,
        ])->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure(["message"]);
    }

    /**
     * Test an exception occurs during payment processing.
     *
     * @return void
     */
    public function test_exception()
    {
        $repayment = Repayment::factory()->create([
            'loan_id' => $this->loan->id,
            'state' => LoanStatus::APPROVED,
        ]);

        // Mock the RepaymentRepositoryInterface to throw an exception
        $mockRepository = $this->createMock(RepaymentRepositoryInterface::class);
        $mockRepository->expects($this->once())
            ->method('payForRepayment')
            ->willThrowException(new \Exception('Test Exception'));
        $this->app->instance(RepaymentRepositoryInterface::class, $mockRepository);

        // Act and Assert
        $response = $this->put(route('repayments.update', ['repayment' => $repayment]), [
            'amount' => $repayment->amount
        ])->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonStructure(["message"]);
    }
}
