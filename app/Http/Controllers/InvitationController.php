<?php

namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\Collocation;
use App\Models\Invitation;
use App\Models\User;
use App\Http\Requests\SendInvitationRequest;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Show the send-invitation form (owner only, enforced by route middleware).
     */
    public function create(Collocation $collocation): View
    {
        $this->authorize('update', $collocation);
        return view('invitation.create', compact('collocation'));
    }

    /**
     * Store a new invitation and send the email.
     */
    public function store(SendInvitationRequest $request, Collocation $collocation): RedirectResponse
    {
        $validated = $request->validated();

        // Don't double-invite the same email for active invitations
        $existing = $collocation->invitations()
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'An active invitation has already been sent to this address.']);
        }

        $invitation = Invitation::create([
            'collocation_id' => $collocation->id,
            'sender_id' => Auth::id(),
            'email' => $validated['email'],
            'token' => Invitation::generateToken(),
            'status' => 'pending',
            'expires_at' => now()->addDays(14),
        ]);

        // Send the email (queue in production)
        Mail::to($validated['email'])->send(new InvitationMail($invitation));

        return redirect()->route('collocation.show', $collocation)
            ->with('status', "Invitation sent to {$validated['email']}.");
    }

    /**
     * Show the join-collocation landing page (public).
     */
    public function show(string $token, MembershipService $membershipService): View|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->isValid()) {
            $reason = $invitation->isExpired() ? 'expired' : $invitation->status;
            return view('invitation.invalid', compact('invitation', 'reason'));
        }

        // Already logged in → accept immediately
        if (Auth::check()) {
            $user = Auth::user();
            // ✅ Validation: vérifier que l'utilisateur n'a pas déjà une colocation active
            $membershipService->validateCanJoinCollocation($user);

            $invitation->accept($user->id);
            return redirect()->route('collocation.show', $invitation->collocation_id)
                ->with('status', "You have joined {$invitation->collocation->name}!");
        }

        // Guest → show join landing page
        return view('invitation.join', compact('invitation'));
    }


    public function accept(string $token, MembershipService $membershipService): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->isValid()) {
            return redirect()->route('dashboard')
                ->withErrors(['invitation' => 'This invitation is no longer valid.']);
        }

        $user = Auth::user();

        // ✅ Validation: vérifier que l'utilisateur n'a pas déjà une colocation active
        $membershipService->validateCanJoinCollocation($user);

        $invitation->accept($user->id);

        return redirect()->route('collocation.show', $invitation->collocation_id)
            ->with('status', "You have joined {$invitation->collocation->name}!");
    }
}
