<x-app-layout>


<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ====== STATS ROW ====== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Reputation --}}
            <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm hover:shadow-md transition">
                <p class="text-sm font-semibold text-[#657e9a] mb-2">My Reputation</p>
                <div class="flex items-end justify-between">
                    <p class="text-3xl font-black text-[#142c3e]">
                        {{ $userReputation ?? 0 }}
                    </p>
                    <span class="text-xs font-semibold text-[#2563eb] bg-[#f4f9ff] px-3 py-1 rounded-full">
                        Score
                    </span>
                </div>
            </div>

            {{-- Global Expenses --}}
            <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm hover:shadow-md transition">
                <p class="text-sm font-semibold text-[#657e9a] mb-2">Global Expenses</p>
                <div class="flex items-end justify-between">
                    <p class="text-3xl font-black text-[#142c3e]">
                        MAD {{ number_format($globalExpenses ?? 0, 2) }}
                    </p>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">
                        Total
                    </span>
                </div>
            </div>

            {{-- Total Debt --}}
            <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm hover:shadow-md transition">
                <p class="text-sm font-semibold text-[#657e9a] mb-2">My Total Debt</p>
                <div class="flex items-end justify-between">
                    <p class="text-3xl font-black text-red-500">
                        MAD {{ number_format($globalDebt ?? 0, 2) }}
                    </p>
                    <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">
                        Owed
                    </span>
                </div>
            </div>

        </div>


        {{-- ====== MAIN CONTENT ====== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Recent Expenses --}}
            <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-extrabold text-[#1b364b]">Recent Expenses</h3>
                </div>

                @if ($recentExpenses->isEmpty())
                    <p class="text-[#657e9a] text-sm">No expenses recorded yet.</p>
                @else
                    <ul class="divide-y divide-[#f0f4f8]">
                        @foreach ($recentExpenses as $expense)
                            <li class="py-3 flex justify-between items-center gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-[#142c3e] truncate">
                                        {{ $expense->title }}
                                    </p>
                                    <p class="text-xs text-[#657e9a] truncate">
                                        {{ $expense->collocation->name }}
                                    </p>
                                </div>
                                <span class="text-sm font-bold text-[#2563eb] shrink-0">
                                    MAD {{ number_format($expense->amount, 2) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Members Overview --}}
            <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-extrabold text-[#1b364b]">Active Members</h3>
                </div>

                @if ($collocations->isEmpty())
                    <p class="text-[#657e9a] text-sm">No collocations yet.</p>
                @else
                    <ul class="divide-y divide-[#f0f4f8]">
                        @foreach ($collocations as $collocation)
                            @foreach ($collocation->members()->wherePivotNull('left_at')->get() as $member)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-semibold text-[#142c3e]">
                                            {{ $member->name }}
                                        </p>
                                        <p class="text-xs text-[#657e9a]">
                                            {{ $collocation->name }}
                                        </p>
                                    </div>
                                    <span class="text-xs bg-[#f4f9ff] text-[#2563eb] px-3 py-1 rounded-full">
                                        Active
                                    </span>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>

    </div>
</div>

</x-app-layout>
