<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function test_admin_can_approve_the_loan()
    {
        $user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'state' => LoanStatus::PENDING,
        ]);
        $this->actingAs($admin);
        $response = $this->post(route('loans.approve', ['loan' => $loan]));

        $response->assertStatus(Response::HTTP_OK);
        $loan->refresh();
        $this->assertEquals($loan->state, LoanStatus::APPROVED);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_not_approve_the_loan()
    {
        $user = User::factory()->create([
            'role' => UserRole::USER,
        ]);

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'state' => LoanStatus::PENDING,
        ]);
        $this->actingAs($user);
        $response = $this->post(route('loans.approve', ['loan' => $loan]));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_can_view_them_own_loan_only()
    {
        $loan = Loan::factory()->create();
        $user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('loans.show', ['loan' => $loan]));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_can_view_any_loan()
    {
        $loan = Loan::factory()->create();
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);
        $this->actingAs($admin);

        $response = $this->get(route('loans.show', ['loan' => $loan]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_can_create_loan()
    {
        $user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->actingAs($user);

        $amount = '5000';
        $term = 5;
        $response = $this->post(route('loans.store', [
            'amount' => $amount,
            'term' => $term,
        ]));

        $response->assertStatus(Response::HTTP_CREATED);

        $loan = Loan::find($response->decodeResponseJson()['data']['id']);

        $this->assertEquals($loan->amount, $amount);
        $this->assertEquals($loan->term, $term);
        $this->assertEquals($loan->state, LoanStatus::PENDING);

        $repaymentAmount = round($amount / $term, 2);
        $dueDate = Carbon::now()->addWeeks(1); // Start with the next week

        foreach ($loan->repayments as $repayment) {
            $this->assertEquals($repaymentAmount, $repayment->amount);
            $this->assertEquals(LoanStatus::PENDING, $repayment->state);
            $this->assertEquals($dueDate->format('Y-m-d'), Carbon::parse($repayment->due_date)->format('Y-m-d'));
            $dueDate->addWeek(); // Increment due date by 1 week
        }
    }

    public function test_user_cant_create_loan_with_invalid_data()
    {
        $user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->actingAs($user);

        $amount = '5000';
        $term = 5;
        $response = $this->post(route('loans.store', [
            'term' => $term,
        ]));

        $response->assertJsonValidationErrorFor('amount');

        $response = $this->post(route('loans.store', [
            'amount' => $amount,
        ]));

        $response->assertJsonValidationErrorFor('term');
    }

    public function test_user_view_index()
    {
        // Create user
        $user1 = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $user2 = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->actingAs($user1);

        // Create sample loans for the user
        $loan1 = Loan::factory()->create([
            'user_id' => $user1->id,
        ]);
        $loan2 = Loan::factory()->create([
            'user_id' => $user2->id,
        ]);

        // Make the request
        $response = $this->get(route('loans.index'));

        // Assert response status code
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment([
            'id' => $loan1->id,
        ]);
        $response->assertJsonMissing(['id' => $loan2->id]);
    }

    public function test_admin_view_index()
    {
        // Create admin
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);
        $this->actingAs($admin);

        // Create sample loans
        $loan1 = Loan::factory()->create();
        $loan2 = Loan::factory()->create();

        // Make the request
        $response = $this->get(route('loans.index'));

        // Assert response status code
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment([
            'id' => $loan1->id,
            'id' => $loan2->id,
        ]);
    }
}
