<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('collocation.show', $collocation) }}" class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← {{ $collocation->name }}
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Payments</h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('payment.create', $collocation) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all">
                    + Record Payment
                </a>
                <a href="{{ route('payment.index', $collocation) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-gray-200 text-gray-800 rounded-full shadow-sm hover:bg-gray-300 transition-all">
                    ⟳ Refresh
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Status Filter --}}
            <form method="GET" class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-semibold text-[#4b6379]">Filter by Status:</span>
                <a href="{{ route('payment.index', $collocation) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ !$status ? 'bg-[#2563eb] text-white' : 'bg-white border border-[#dae2ec] text-[#4b6379] hover:bg-[#f4f9ff]' }}">
                    All
                </a>
                <a href="{{ route('payment.index', [$collocation, 'status' => 'pending']) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white border border-yellow-300 text-yellow-700 hover:bg-yellow-50' }}">
                    Pending Confirmation
                </a>
                <a href="{{ route('payment.index', [$collocation, 'status' => 'confirmed']) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'confirmed' ? 'bg-orange-500 text-white' : 'bg-white border border-orange-300 text-orange-700 hover:bg-orange-50' }}">
                    Confirmed
                </a>
                <a href="{{ route('payment.index', [$collocation, 'status' => 'completed']) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'completed' ? 'bg-green-500 text-white' : 'bg-white border border-green-300 text-green-700 hover:bg-green-50' }}">
                    Completed
                </a>
                <a href="{{ route('payment.index', [$collocation, 'status' => 'rejected']) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $status === 'rejected' ? 'bg-red-500 text-white' : 'bg-white border border-red-300 text-red-700 hover:bg-red-50' }}">
                    Rejected
                </a>
            </form>

            {{-- Summary Cards --}}
            @if($payments->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <div class="text-xs font-semibold text-yellow-700 uppercase tracking-wider">Pending</div>
                        <div class="mt-2 text-2xl font-bold text-yellow-900">
                            €{{ number_format($collocation->payments()->where('status', 'pending')->sum('amount'), 2) }}
                        </div>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                        <div class="text-xs font-semibold text-orange-700 uppercase tracking-wider">Confirmed</div>
                        <div class="mt-2 text-2xl font-bold text-orange-900">
                            €{{ number_format($collocation->payments()->where('status', 'confirmed')->sum('amount'), 2) }}
                        </div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="text-xs font-semibold text-green-700 uppercase tracking-wider">Completed</div>
                        <div class="mt-2 text-2xl font-bold text-green-900">
                            €{{ number_format($collocation->payments()->where('status', 'completed')->sum('amount'), 2) }}
                        </div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="text-xs font-semibold text-red-700 uppercase tracking-wider">Rejected</div>
                        <div class="mt-2 text-2xl font-bold text-red-900">
                            €{{ number_format($collocation->payments()->where('status', 'rejected')->sum('amount'), 2) }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payment list --}}
            @if($payments->isEmpty())
                <div class="bg-white rounded-2xl border border-[#dae2ec] p-12 text-center">
                    <p class="text-[#657e9a] text-sm mb-4">No payments found.</p>
                    <a href="{{ route('collocation.show', $collocation) }}"
                       class="inline-block px-6 py-3 bg-[#2563eb] text-white font-bold text-sm rounded-full hover:bg-[#1a4ac4] transition-all">
                        Go to Collocation
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($payments as $payment)
                        <div class="bg-white rounded-xl border border-[#dae2ec] p-4 hover:shadow-md transition-shadow">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                {{-- Left Side: Payment Info --}}
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-[#142c3e]">
                                                {{ $payment->payer->name }} → {{ $payment->receiver->name }}
                                            </p>
                                            <p class="text-xs text-[#657e9a] mt-0.5">
                                                {{ $payment->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-[#142c3e]">
                                                €{{ number_format($payment->amount, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Badge --}}
                                <div class="flex items-center gap-3">
                                    @if($payment->isPending())
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-300">
                                            Pending Confirmation
                                        </span>
                                    @elseif($payment->isConfirmed())
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-300">
                                            Confirmed
                                        </span>
                                    @elseif($payment->isCompleted())
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                            ✓ Completed
                                        </span>
                                        @if($payment->paid_at)
                                            <p class="text-xs text-[#657e9a]">
                                                Paid: {{ $payment->paid_at->format('d M Y') }}
                                            </p>
                                        @endif
                                    @elseif($payment->isRejected())
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                            Rejected
                                        </span>
                                    @elseif($payment->isCancelled())
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-300">
                                            Cancelled
                                        </span>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex flex-wrap gap-2 justify-end md:justify-start">
                                    {{-- Receiver can confirm pending payments --}}
                                    @if($payment->isPending() && auth()->id() === $payment->receiver_id)
                                        <form action="{{ route('payment.confirm', $payment) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-bold bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                                ✓ Confirm
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Payer can mark confirmed payments as complete --}}
                                    @if($payment->isConfirmed() && auth()->id() === $payment->payer_id)
                                        <form action="{{ route('payment.complete', $payment) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-bold bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                                Mark Paid
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Show reject option --}}
                                    @if(!$payment->isCompleted() && !$payment->isCancelled() &&
                                        (auth()->id() === $payment->receiver_id || auth()->id() === $payment->payer_id))
                                        <details class="group">
                                            <summary class="px-3 py-1.5 text-xs font-bold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors cursor-pointer list-none">
                                                ⋯ More
                                            </summary>
                                            <div class="absolute right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 min-w-[180px]">
                                                @if(auth()->id() === $payment->receiver_id && $payment->isPending())
                                                    <button onclick="openRejectModal({{ $payment->id }})"
                                                            class="w-full px-4 py-2 text-xs text-left text-red-600 hover:bg-red-50 font-bold border-b border-gray-200">
                                                        Reject Payment
                                                    </button>
                                                @endif
                                                @if(auth()->id() === $payment->payer_id && $payment->isPending())
                                                    <form action="{{ route('payment.cancel', $payment) }}" method="POST" class="w-full">
                                                        @csrf
                                                        <button type="submit"
                                                                class="w-full px-4 py-2 text-xs text-left text-gray-600 hover:bg-gray-100 font-bold"
                                                                onclick="return confirm('Cancel this payment?')">
                                                            Cancel Payment
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>

                            {{-- Rejection reason if rejected --}}
                            @if($payment->isRejected() && $payment->rejection_reason)
                                <div class="mt-3 pt-3 border-t border-red-200">
                                    <p class="text-xs text-red-700 font-italic">
                                        <strong>Reason:</strong> {{ $payment->rejection_reason }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $payments->links() }}</div>
            @endif

        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md mx-4">
            <h3 class="text-lg font-bold text-[#142c3e] mb-4">Reject Payment</h3>
            <form id="rejectForm" action="" method="POST">
                @csrf
                <textarea
                    name="reason"
                    placeholder="Why are you rejecting this payment?"
                    class="w-full px-3 py-2 border border-[#dae2ec] rounded-lg text-sm focus:outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 resize-none"
                    rows="4"></textarea>
                <div class="flex gap-3 mt-4">
                    <button type="button"
                            onclick="closeRejectModal()"
                            class="flex-1 px-4 py-2 text-sm font-bold text-[#657e9a] border border-[#dae2ec] rounded-lg hover:bg-[#f4f9ff] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 text-sm font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(paymentId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/payments/${paymentId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    </script>
</x-app-layout>
