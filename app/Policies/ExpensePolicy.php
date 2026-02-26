<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Whether the user can update this expense.
     * - Author (member_id) can always update their own.
     * - Collocation owner can update anyone's expense in their collocation.
     */
    public function update(User $user, Expense $expense): bool
    {
        if ($user->id === $expense->member_id) {
            return true;
        }

        return $user->isOwner()
            && $expense->collocation->owner_id === $user->id;
    }

    /**
     * Same rules for deletion.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $this->update($user, $expense);
    }
}
