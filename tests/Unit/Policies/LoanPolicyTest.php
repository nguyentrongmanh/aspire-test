<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Loan;
use Tests\TestCase;

class LoanPolicyTest extends TestCase
{
    /**
     * Test admin can view all loan.
     *
     * @return void
     */
    public function test_admin_can_view_all_loan()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['role' => UserRole::USER]);
        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($admin->can("view", $loan));
    }

    /**
     * Test users can view their own loan.
     *
     * @return void
     */
    public function test_users_can_view_their_own_loan()
    {
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);
        $loan = Loan::factory()->create(['user_id' => $user1->id]);

        $this->assertTrue($user1->can("view", $loan));
        $this->assertFalse($user2->can("view", $loan));
    }

    /**
     * Test only admin can approve a loan.
     *
     * @return void
     */
    public function test_only_admin_can_approve_a_loans()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($user->can("approve", $loan));
        $this->assertTrue($admin->can("approve", $loan));
    }
}
