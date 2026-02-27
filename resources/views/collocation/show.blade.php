<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('collocation.index') }}"
                    class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← My Collocations
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                    {{ $collocation->name }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                {{-- Status badge --}}
                @if ($collocation->isCancelled())
                    <span
                        class="bg-red-50 text-red-500 text-xs font-bold px-4 py-1.5 rounded-full border border-red-200">Cancelled</span>
                @elseif($collocation->status === 'active')
                    <span
                        class="bg-[#f4f9ff] text-[#2563eb] text-xs font-bold px-4 py-1.5 rounded-full border border-[#2563eb]/20">Active</span>
                @else
                    <span
                        class="bg-gray-50 text-gray-500 text-xs font-bold px-4 py-1.5 rounded-full border border-gray-200">Inactive</span>
                @endif

                @can('update', $collocation)
                    <a href="{{ route('collocation.edit', $collocation) }}"
                        class="text-sm px-5 py-2 border border-[#d3e0f0] rounded-full font-bold text-[#2563eb] bg-white hover:bg-[#f2f9ff] transition-all">
                        Edit
                    </a>
                @endcan

                {{-- Leave button: only for active members (not pure 'user' role). Owner cannot leave. --}}
                @if((Auth::user()->isMember() || Auth::user()->isOwner()) && Auth::id() !== $collocation->owner_id)
                    <form action="{{ route('collocation.leave', $collocation) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to leave this collocation? This may affect your reputation score if you have outstanding balances.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-sm px-5 py-2 border border-red-200 rounded-full font-bold text-red-600 bg-white hover:bg-red-50 transition-all">
                            Leave
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash status --}}
            @if (session('status'))
                <div class="px-5 py-4 bg-[#f4f9ff] border border-[#2563eb]/20 rounded-2xl text-[#2563eb] font-semibold text-sm flex justify-between items-center flex-wrap gap-4">
                    <span>{{ session('status') }}</span>
                    @if(session('invitation_token'))
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-white border border-[#2563eb]/20 px-3 py-1.5 rounded text-[#657e9a] font-mono truncate max-w-[200px]" title="{{ session('invitation_token') }}">
                                {{ substr(session('invitation_token'), 0, 16) }}...
                            </span>
                            <button onclick="navigator.clipboard.writeText('{{ session('invitation_token') }}'); this.innerText='Copied!';" class="text-xs font-bold text-white bg-[#2563eb] px-3 py-1.5 rounded hover:bg-[#1a4ac4] transition-colors shrink-0">
                                Copy Token
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Top grid: Expenses left, Who owes whom right --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Expenses Section --}}
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-extrabold text-[#1b364b]">Expenses</h3>
                        @can('view', $collocation)
                            <a href="{{ route('expense.create', $collocation) }}"
                                class="text-sm font-bold text-white bg-[#2563eb] px-4 py-2 rounded-full hover:bg-[#1e4db7] transition-all">
                                + Add Expense
                            </a>
                        @endcan
                    </div>
                    @if ($collocation->expenses->isEmpty())
                        <p class="text-sm text-[#657e9a]">No expenses added yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($collocation->expenses as $expense)
                                <li class="flex justify-between items-center border-b border-[#e0e5eb] py-2">
                                    <div>
                                        <p class="text-sm font-bold text-[#142c3e]">{{ $expense->title }}</p>
                                        <p class="text-xs text-[#657e9a]">
                                            {{ $expense->category->name ?? 'No Category' }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-[#2563eb]">€{{ number_format($expense->amount, 2) }}</span>

                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Who owes whom Section --}}
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-extrabold text-[#1b364b]">Who owes whom</h3>
                        <a href="{{ route('payment.index', $collocation) }}"
                           class="text-xs font-bold text-[#2563eb] hover:underline">View all payments →</a>
                    </div>
                    @php
                        $activeMemberIds = $collocation->members()->wherePivotNull('left_at')->pluck('users.id')->toArray();
                        if (!in_array($collocation->owner_id, $activeMemberIds)) {
                            $activeMemberIds[] = $collocation->owner_id;
                        }
                        $activeExpenseShares = array_filter($expenseShares, fn($share) =>
                            in_array($share['user_id'], $activeMemberIds)
                        );
                    @endphp

                    @if (empty($activeExpenseShares))
                        <div class="text-center py-6">
                            <div class="text-3xl mb-2">✅</div>
                            <p class="text-sm font-semibold text-[#142c3e]">All settled up!</p>
                            <p class="text-xs text-[#657e9a] mt-1">No outstanding balances.</p>
                        </div>
                    @else
                        <div class="space-y-5">
                            @foreach ($activeExpenseShares as $userShare)
                                @php
                                    $visibleShares = array_filter($userShare['shares'], fn($s) =>
                                        $s['payer_id'] !== $s['receiver_id']
                                    );
                                @endphp
                                @if (!empty($visibleShares))
                                    <div class="border border-[#e8eef5] rounded-xl overflow-hidden">
                                        <div class="flex items-center gap-3 px-4 py-3 bg-[#f4f9ff] border-b border-[#e0e8f2]">
                                            <div class="w-8 h-8 rounded-full bg-[#2563eb]/15 flex items-center justify-center shrink-0">
                                                <span class="text-[#2563eb] text-xs font-black">{{ strtoupper(substr($userShare['user_name'], 0, 2)) }}</span>
                                            </div>
                                            <span class="text-sm font-bold text-[#142c3e]">{{ $userShare['user_name'] }}</span>
                                            @if(auth()->id() === $userShare['user_id'])
                                                <span class="ml-1 text-xs bg-[#2563eb] text-white px-2 py-0.5 rounded-full font-semibold">You</span>
                                            @endif
                                        </div>
                                        <ul class="divide-y divide-[#f0f4f8]">
                                            @foreach ($visibleShares as $share)
                                                <li class="px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-semibold text-[#142c3e] truncate">
                                                                Owes <span class="text-[#2563eb]">{{ $share['receiver_name'] }}</span>
                                                            </p>
                                                            <p class="text-[10px] text-[#657e9a] truncate mt-0.5">{{ $share['expense_title'] }}</p>
                                                        </div>
                                                        <span class="text-sm font-black text-[#142c3e] shrink-0">
                                                            €{{ number_format($share['amount'], 2) }}
                                                        </span>
                                                        @if($share['payed'])
                                                            <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold bg-green-50 text-green-700 px-2.5 py-1 rounded-full border border-green-200">
                                                                ✓ Paid
                                                            </span>
                                                        @elseif(auth()->id() === $userShare['user_id'])
                                                            <div class="flex items-center gap-2 shrink-0">
                                                                <form action="{{ route('share.pay', $share['share_id']) }}" method="POST"
                                                                      onsubmit="return confirm('Mark this share as paid?')">
                                                                    @csrf
                                                                    <button type="submit"
                                                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors shadow-sm">
                                                                        ✓ Pay Now
                                                                    </button>
                                                                </form>
                                                                <button type="button"
                                                                        onclick="openPaymentModal({{ $share['receiver_id'] }}, '{{ addslashes($share['receiver_name']) }}', {{ $share['amount'] }})"
                                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold bg-[#2563eb] hover:bg-[#1a4ac4] text-white rounded-lg transition-colors shadow-sm">
                                                                    💳 Track
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="shrink-0 inline-flex items-center text-[10px] font-bold bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full border border-amber-200">
                                                                Pending
                                                            </span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @php
                            $myTotalDebt = 0;
                            foreach ($activeExpenseShares as $g) {
                                if ($g['user_id'] == auth()->id()) {
                                    foreach ($g['shares'] as $s) {
                                        if (!$s['payed']) {
                                            $myTotalDebt += $s['amount'];
                                        }
                                    }
                                }
                            }
                        @endphp
                        @if($myTotalDebt > 0)
                            <div class="mt-4 pt-4 border-t border-[#e0e5eb] flex items-center justify-between">
                                <span class="text-xs font-semibold text-[#657e9a]">Your total outstanding</span>
                                <span class="text-base font-black text-red-600">€{{ number_format($myTotalDebt, 2) }}</span>
                            </div>
                        @endif
                    @endif
                </div>

            </div>

            {{-- Members preview --}}
            <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-extrabold text-[#1b364b]">Members</h3>
                    <a href="{{ route('collocation.members', $collocation) }}"
                        class="text-sm font-bold text-[#2563eb] hover:underline">View all →</a>
                </div>

                @php
                    // Get only active members (those who haven't left)
                    $activeMembers = $collocation->members()->wherePivotNull('left_at')->get();
                @endphp

                @if ($activeMembers->isEmpty())
                    <p class="text-sm text-[#657e9a]">No active members yet. Invite people to join this collocation.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($activeMembers as $member)
                            <li class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 bg-[#2563eb]/10 rounded-full flex items-center justify-center shrink-0">
                                    <span
                                        class="text-[#2563eb] text-sm font-bold">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-[#142c3e] truncate">
                                        {{ $member->name }}
                                        @if ($member->id === $collocation->owner_id)
                                            <span class="text-xs text-[#2563eb] font-semibold">(Owner)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-[#657e9a] truncate">{{ $member->email }}</p>
                                </div>
                                <p class="text-xs text-[#657e9a] shrink-0">⭐ {{ $member->reputation_score ?? 0 }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Danger zone — Cancel --}}
            @can('cancel', $collocation)
                <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
                    <h3 class="text-base font-extrabold text-red-600 mb-2">Danger Zone</h3>
                    <p class="text-sm text-[#657e9a] mb-5">
                        Cancelling this collocation is irreversible. Members will no longer be able to add expenses.
                    </p>
                    <form method="POST" action="{{ route('collocation.cancel', $collocation) }}"
                        onsubmit="return confirm('Are you sure you want to cancel this collocation?');">
                        @csrf
                        <x-danger-button>
                            Cancel this Collocation
                        </x-danger-button>
                    </form>
                </div>
            @endcan

        </div>
    </div>

    {{-- Payment Modal --}}
    <div id="paymentModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-[#142c3e] mb-4">Record Payment</h3>
            <form id="paymentForm" method="POST" action="{{ route('payment.store', $collocation) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-[#142c3e] mb-2">Pay to:</label>
                    <input type="hidden" id="receiverId" name="receiver_id">
                    <div id="receiverDisplay" class="px-4 py-2 bg-[#f4f9ff] border border-[#2563eb]/20 rounded-lg text-sm font-semibold text-[#2563eb]"></div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#142c3e] mb-2">Amount (€):</label>
                    <input type="number"
                           id="paymentAmount"
                           name="amount"
                           step="0.01"
                           min="0.01"
                           required
                           readonly
                           class="w-full px-4 py-2 border border-[#dae2ec] rounded-lg text-sm bg-gray-50 font-bold text-[#2563eb]">
                </div>

                <p class="text-xs text-[#657e9a] bg-[#f4f9ff] border border-[#2563eb]/20 rounded-lg p-3">
                    ℹ️ This will record the payment. The receiver will need to confirm they received it.
                </p>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="closePaymentModal()"
                            class="flex-1 px-4 py-2 text-sm font-bold text-[#657e9a] border border-[#dae2ec] rounded-lg hover:bg-[#f4f9ff] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 text-sm font-bold text-white bg-[#2563eb] rounded-lg hover:bg-[#1a4ac4] transition-colors">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(receiverId, receiverName, amount) {
            document.getElementById('receiverId').value = receiverId;
            document.getElementById('receiverDisplay').textContent = receiverName;
            document.getElementById('paymentAmount').value = parseFloat(amount).toFixed(2);
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('paymentModal')?.addEventListener('click', function(e) {
            if (e.target === this) closePaymentModal();
        });
    </script>
</x-app-layout>
