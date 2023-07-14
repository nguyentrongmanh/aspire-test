<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Repayment $repayment)
    {
        return $user->role == UserRole::ADMIN || $repayment->loan->user_id == $user->id;
    }
}
