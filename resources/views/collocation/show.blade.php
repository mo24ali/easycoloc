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
                @if($collocation->isCancelled())
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
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash status --}}
            @if(session('status'))
                <div
                    class="px-5 py-4 bg-[#f4f9ff] border border-[#2563eb]/20 rounded-2xl text-[#2563eb] font-semibold text-sm">
                    {{ session('status') }}
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
                    @if($collocation->expenses->isEmpty())
                        <p class="text-sm text-[#657e9a]">No expenses added yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($collocation->expenses as $expense)
                                <li class="flex justify-between items-center border-b border-[#e0e5eb] py-2">
                                    <div>
                                        <p class="text-sm font-bold text-[#142c3e]">{{ $expense->title }}</p>
                                        <p class="text-xs text-[#657e9a]">{{ $expense->category->name ?? 'No Category' }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-[#2563eb]">${{ $expense->amount }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Who owes whom Section --}}
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-lg font-extrabold text-[#1b364b] mb-4">Who owes whom</h3>
                    @if(empty($expenseShares))
                        <p class="text-sm text-[#657e9a]">No expenses shared yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($expenseShares as $userShare)
                                <div class="border border-[#e0e5eb] rounded-lg p-4">
                                    <p class="text-sm font-bold text-[#142c3e] mb-3">{{ $userShare['user_name'] }}</p>
                                    <ul class="space-y-2 ml-2">
                                        @foreach($userShare['shares'] as $share)
                                            <li class="text-xs text-[#657e9a] border-l-2 border-[#2563eb] pl-3">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-[#142c3e]">owes {{ $share['receiver_name'] }}</p>
                                                        <p class="text-[#657e9a]">{{ $share['expense_title'] }}</p>
                                                    </div>
                                                    <span class="font-bold text-[#2563eb] ml-2 shrink-0">${{ number_format($share['amount'], 2) }}</span>
                                                </div>
                                                @if($share['payed'])
                                                    <span class="inline-block text-[10px] bg-green-50 text-green-600 px-2 py-1 rounded mt-1 border border-green-200">✓ Paid</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
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

                @if($collocation->members->isEmpty())
                    <p class="text-sm text-[#657e9a]">No members yet. Invite people to join this collocation.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($collocation->members as $member)
                            <li class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-[#2563eb]/10 rounded-full flex items-center justify-center shrink-0">
                                    <span
                                        class="text-[#2563eb] text-sm font-bold">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-[#142c3e] truncate">{{ $member->name }}</p>
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
</x-app-layout>
