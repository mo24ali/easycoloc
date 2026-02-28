<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Models\Collocation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Exception;

class InvitationService
{
    /**
     * Invite a user dynamically checking explicitly mapped rules.
     */
    public function invite(Collocation $collocation, string $email, int $senderId): Invitation
    {
        $existing = $collocation->invitations()
            ->where('email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            throw new Exception('An active invitation has already been sent to this address.');
        }

        $invitation = Invitation::create([
            'collocation_id' => $collocation->id,
            'sender_id' => $senderId,
            'email' => $email,
            'token' => Invitation::generateToken(),
            'status' => 'pending',
            'expires_at' => now()->addDays(14),
        ]);

        // Send the email (queue in production)
        Mail::to($email)->send(new InvitationMail($invitation));

        return $invitation;
    }
}
