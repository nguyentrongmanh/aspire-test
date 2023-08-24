<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\User;
use App\Interfaces\LoanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'state' => LoanStatus::PENDING,
        ]);
    }

    /**
     * Test admin can approve a loan
     *
     * @return void
     */
    public function test_admin_approve_the_loan()
    {
        $this->actingAs($this->admin);

        // Act
        $this->post(route('loans.approve', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_OK);

        // Assert
        $this->loan->refresh();
        $this->assertEquals($this->loan->state, LoanStatus::APPROVED);
    }

    /**
     * User doesn't have permission to approve a loan.
     *
     * @return void
     */
    public function test_user_can_not_approve_the_loan()
    {
        $this->actingAs($this->user);
        $this->post(route('loans.approve', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJsonStructure(["message"]);
    }

    /**
     * Test can't approve a loan due to an exception.
     *
     * @return void
     */
    public function test_approve_loan_exception()
    {
        $this->actingAs($this->admin);

        // Mock the LoanRepositoryInterface to throw an exception
        $mockRepository = $this->createMock(LoanRepositoryInterface::class);
        $mockRepository->expects($this->once())
            ->method('approveLoan')
            ->willThrowException(new \Exception('Test Exception'));
        $this->app->instance(LoanRepositoryInterface::class, $mockRepository);

        // Act and assert
        $this->post(route('loans.approve', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonStructure(["message"]);
    }

    /**
     * User can create a loan with valid data.
     * 
     * @return void
     */
    public function test_user_can_create_loan()
    {
        $this->actingAs($this->user);

        // input data
        $amount = '5000';
        $term = 5;

        // Act
        $response = $this->post(route('loans.store', [
            'amount' => $amount,
            'term' => $term,
        ]));

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);

        // Verify loan details in the database
        $loan = Loan::find($response->decodeResponseJson()['data']['id']);
        $this->assertEquals($loan->amount, $amount);
        $this->assertEquals($loan->term, $term);
        $this->assertEquals($loan->state, LoanStatus::PENDING);

        // Verify repayment details
        $repaymentAmount = round($amount / $term, 2);
        $dueDate = Carbon::now()->addWeeks(1); // Start with the next week
        foreach ($loan->repayments as $repayment) {
            $this->assertEquals($repaymentAmount, $repayment->amount);
            $this->assertEquals(LoanStatus::PENDING, $repayment->state);
            $this->assertEquals($dueDate->format('Y-m-d'), Carbon::parse($repayment->due_date)->format('Y-m-d'));
            $dueDate->addWeek(); // Increment due date by 1 week
        }
    }

    /**
     * User can not create a loan with invalid data.
     * 
     * @return void
     */
    public function test_user_cant_create_loan_with_invalid_data()
    {
        $this->actingAs($this->user);

        $amount = '5000';
        $term = 5;
        $this->post(route('loans.store', [
            'term' => $term,
        ]))->assertJsonValidationErrorFor('amount')
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->post(route('loans.store', [
            'amount' => $amount,
        ]))->assertJsonValidationErrorFor('term')
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test creating a loan encounters an exception.
     * 
     * @return void
     */
    public function test_create_loan_exception()
    {
        $this->actingAs($this->user);

        // input data
        $amount = '5000';
        $term = 5;

        // Mock the LoanRepositoryInterface to throw an exception
        $mockRepository = $this->createMock(LoanRepositoryInterface::class);
        $mockRepository->expects($this->once())
            ->method('createLoan')
            ->willThrowException(new \Exception('Test Exception'));
        $this->app->instance(LoanRepositoryInterface::class, $mockRepository);

        // Act and Assert
        $response = $this->post(route('loans.store', [
            'term' => $term,
            'amount' => $amount
        ]))->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonStructure(["message"]);
    }

    /**
     * Test user can only view their own loan details.
     * 
     * @return void
     */
    public function test_user_can_view_them_own_loan_only()
    {
        $newUser = User::factory()->create([
            'role' => UserRole::USER,
        ]);
        $this->actingAs($newUser);

        $this->get(route('loans.show', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($this->user);
        $this->get(route('loans.show', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([ "data" => [
                "id", 
                "amount",
                "term"
            ]]);
    }

    /**
     * Test admin can view any loan detail.
     * 
     * @return void
     */
    public function test_admin_can_view_any_loan_detail()
    {
        $this->actingAs($this->admin);
        $this->get(route('loans.show', ['loan' => $this->loan]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([ "data" => [
                "id", 
                "amount",
                "term"
            ]]);
    }

    /**
     * Test user can view their own loans only in the index.
     * 
     * @return void
     */
    public function test_user_view_own_loans_in_index()
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

        // act and assert
        $this->get(route('loans.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['id' => $loan1->id])
            ->assertJsonMissing(['id' => $loan2->id]);
    }

    /**
     * Test admin can view all loans in the index.
     * 
     * @return void
     */
    public function test_admin_view_all_loan_in_index()
    {
        $this->actingAs($this->admin);

        // Create sample loans
        $loan1 = Loan::factory()->create();
        $loan2 = Loan::factory()->create();

        // act and assert
        $this->get(route('loans.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $loan1->id,
                'id' => $loan2->id,
            ]);
    }
}
