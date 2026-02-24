<?php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecurringTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }
}
