<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {
    }

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
     * Show form to create a new payment.
     */
    public function create(Collocation $collocation): View
    {
        $this->authorize('view', $collocation);

        return view('payment.create', compact('collocation'));
    }

    /**
     * Store a new payment record (payer → receiver).
     */
    public function store(StorePaymentRequest $request, Collocation $collocation): RedirectResponse
    {
        $this->authorize('view', $collocation);

        $validated = $request->validated();

        Payment::create([
            'collocation_id' => $collocation->id,
            'payer_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return redirect()->route('payment.index', $collocation)
            ->with('success', 'Payment recorded successfully. Waiting for receiver confirmation.');
    }

    /**
     * Receiver confirms/accepts the payment.
     */
    public function confirm(Payment $payment): RedirectResponse
    {
        $this->authorize('confirm', $payment);

        if ($payment->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending payments can be confirmed.');
        }

        $payment->update(['status' => 'confirmed']);

        return redirect()->back()
            ->with('success', "Payment of €{$payment->amount} confirmed. Waiting for payer to mark as paid.");
    }

    /**
     * Mark a payment as completed (paid) and clear related expense shares.
     */
    public function complete(Payment $payment): RedirectResponse
    {
        $this->authorize('complete', $payment);

        if (!in_array($payment->status, ['pending', 'confirmed'])) {
            return redirect()->back()
                ->with('error', 'This payment cannot be marked as complete.');
        }

        $payment->update([
            'status' => 'completed',
            'paid_at' => now()
        ]);

        // Mark all related unpaid expense_shares as paid.
        // Logic: payer_id = who paid (debtor), and the expense must have been
        // created by the receiver (member_id = payment.receiver_id) in this collocation.
        \App\Models\ExpenseShare::where('payer_id', $payment->payer_id)
            ->where('payed', false)
            ->whereHas('expense', function ($q) use ($payment) {
                $q->where('collocation_id', $payment->collocation_id)
                    ->where('member_id', $payment->receiver_id);
            })
            ->update(['payed' => true]);

        return redirect()->back()
            ->with('success', "Payment of €{$payment->amount} marked as completed. Related shares cleared.");
    }

    /**
     * Reject or dispute a payment.
     */
    public function reject(Payment $payment, Request $request): RedirectResponse
    {
        $this->authorize('reject', $payment);

        if ($payment->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot reject a completed payment.');
        }

        $reason = $request->input('reason');

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);

        return redirect()->back()
            ->with('success', 'Payment rejected.');
    }

    /**
     * Cancel a pending payment.
     */
    public function cancel(Payment $payment): RedirectResponse
    {
        $this->authorize('cancel', $payment);

        if ($payment->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending payments can be cancelled.');
        }

        $payment->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Payment cancelled.');
    }
}
