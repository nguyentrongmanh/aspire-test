<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class RepaymentTest extends TestCase
{
    public function test_user_can_pay_for_repayment()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        $this->actingAs($user);

        $loan = Loan::factory()->create(['user_id' => $user->id, 'state' => LoanStatus::APPROVED]);
        $repayments = Repayment::factory()->count($loan->term)->create([
            'loan_id' => $loan->id,
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

        $loan->refresh();

        $this->assertEquals(LoanStatus::PAID, $loan->state);
    }

    public function test_user_cant_pay_with_insufficient_payment_amount()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        $this->actingAs($user);

        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $repayment = Repayment::factory()->create([
            'loan_id' => $loan->id,
            'state' => LoanStatus::APPROVED,
        ]);

        $paymentAmount = $repayment->amount - 1;
        $response = $this->put(route('repayments.update', ['repayment' => $repayment]), [
            'amount' => $paymentAmount,
        ])->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonValidationErrorFor('amount');
    }

    public function test_user_cant_pay_for_unapproved_loan()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        $this->actingAs($user);

        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $repayment = Repayment::factory()->create([
            'loan_id' => $loan->id,
            'state' => LoanStatus::PENDING,
        ]);

        $response = $this->put(route('repayments.update', ['repayment' => $repayment]), [
            'amount' => $repayment->amount,
        ])->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
