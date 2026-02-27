<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Whether the user can view the payment.
     * - Both payer and receiver can view
     * - Collocation owner can view all payments in their collocation
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->id === $payment->payer_id || $user->id === $payment->receiver_id) {
            return true;
        }

        return $user->isOwner()
            && $payment->collocation->owner_id === $user->id;
    }

    /**
     * Whether the user can confirm a payment (receiver confirms).
     */
    public function confirm(User $user, Payment $payment): bool
    {
        return $user->id === $payment->receiver_id;
    }

    /**
     * Whether the user can mark payment as complete (payer marks as paid).
     */
    public function complete(User $user, Payment $payment): bool
    {
        return $user->id === $payment->payer_id;
    }

    /**
     * Whether the user can reject/dispute a payment.
     */
    public function reject(User $user, Payment $payment): bool
    {
        return $user->id === $payment->payer_id || $user->id === $payment->receiver_id;
    }

    /**
     * Whether the user can cancel a pending payment (payer can cancel).
     */
    public function cancel(User $user, Payment $payment): bool
    {
        return $user->id === $payment->payer_id;
    }
}
