<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * List payments for a collocation.
     */
    public function index(Collocation $collocation): View
    {
        $this->authorize('view', $collocation);

        $status = request('status');

        $payments = $collocation->payments()
            ->with(['payer', 'receiver'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('payment.index', compact('collocation', 'payments', 'status'));
    }

    /**
     * Store a new payment record (payer → receiver).
     */
    public function store(Request $request, Collocation $collocation): RedirectResponse
    {
        $this->authorize('view', $collocation);

        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id', 'different:' . Auth::id()],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        Payment::create([
            'collocation_id' => $collocation->id,
            'payer_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return redirect()->route('payment.index', $collocation)
            ->with('status', 'Payment recorded. Mark it as completed once done.');
    }

    /**
     * Mark a payment as completed.
     */
    public function complete(Payment $payment): RedirectResponse
    {
        // Only the payer can confirm completion
        if ($payment->payer_id !== Auth::id()) {
            abort(403);
        }

        $payment->update(['status' => 'completed', 'paid_at' => now()]);

        return redirect()->back()
            ->with('status', "Payment of €{$payment->amount} marked as completed.");
    }

    /**
     * Cancel a pending payment.
     */
    public function cancel(Payment $payment): RedirectResponse
    {
        if ($payment->payer_id !== Auth::id()) {
            abort(403);
        }

        $payment->update(['status' => 'cancelled']);

        return redirect()->back()->with('status', 'Payment cancelled.');
    }
}
