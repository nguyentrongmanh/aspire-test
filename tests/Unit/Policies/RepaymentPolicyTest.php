<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Loan;
use App\Models\Repayment;
use Tests\TestCase;

class RepaymentPolicyTest extends TestCase
{
    /**
     * Test admin can update repayment
     *
     * @return void
     */
    public function test_admin_can_update_repayment()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $repayment = Repayment::factory()->create();

        $this->assertTrue($admin->can("update", $repayment));
    }

    /**
     * Test user can update own repayment
     *
     * @return void
     */
    public function test_user_can_update_own_repayment()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        $loan = Loan::factory()->create(['user_id' => $user->id]);
        $repayment = Repayment::factory()->create(['loan_id' => $loan->id]);

        $this->assertTrue($user->can("update", $repayment));
    }

    /**
     * Test user can not update other users payment
     *
     * @return void
     */
    public function test_user_can_not_update_other_users_repayment()
    {
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);
        $loan = Loan::factory()->create(['user_id' => $user1->id]);
        $repayment = Repayment::factory()->create(['loan_id' => $loan->id]);

        $this->assertFalse($user2->can("update", $repayment));
    }
}
