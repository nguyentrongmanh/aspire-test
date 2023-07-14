<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Loan $loan)
    {
        return $user->role == UserRole::ADMIN || $loan->user_id == $user->id;
    }

    /**
     * Determine whether the user can approve the loan.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(User $user, Loan $loan)
    {
        return $user->role == UserRole::ADMIN;
    }
}
