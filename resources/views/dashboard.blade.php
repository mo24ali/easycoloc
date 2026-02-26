<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs text-[#657e9a] font-semibold mb-0.5">Welcome back</p>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                    {{ Auth::user()->name }}
                    <span
                        class="text-base font-semibold text-[#657e9a] capitalize ml-2">({{ Auth::user()->role }})</span>
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                @if (Auth::user()->isOwner())
                    <a href="{{ route('collocation.create') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all">
                        + New Collocation
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                {{-- ================= LEFT : MY COLLOCATIONS ================= --}}
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-extrabold text-[#1b364b]">
                            @if (Auth::user()->isOwner())
                                My Collocations
                            @else
                                My Colocation
                            @endif
                            <span class="text-sm font-normal text-[#657e9a] ml-2">
                                ({{ $collocations->count() }})
                            </span>
                        </h3>

                        @if (Auth::user()->isOwner())
                            <a href="{{ route('collocation.create') }}"
                                class="text-sm font-bold text-[#2563eb] hover:underline">
                                + New
                            </a>
                        @endif
                    </div>

                    @if ($collocations->isEmpty())
                        <div class="bg-white rounded-3xl border border-[#e2e8f0] p-10 text-center shadow-sm">
                            <p class="text-[#64748b] text-sm">
                                No collocations yet.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($collocations as $collocation)
                                <div
                                    class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm hover:shadow-md transition-all">

                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-[#0f172a] text-lg">
                                            {{ $collocation->name }}
                                        </h4>

                                        @if ($collocation->isCancelled())
                                            <span
                                                class="bg-red-50 text-red-500 text-xs font-bold px-3 py-1 rounded-full">
                                                Cancelled
                                            </span>
                                        @else
                                            <span
                                                class="bg-emerald-50 text-emerald-600 text-xs font-bold px-3 py-1 rounded-full">
                                                Active
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-sm text-[#64748b] mt-2 flex gap-6">
                                        <span>👥 {{ $collocation->members_count }}
                                            {{ Str::plural('member', $collocation->members_count) }}</span>
                                        <span>📅 {{ $collocation->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="flex gap-3 mt-5">
                                        <a href="{{ route('collocation.show', $collocation) }}"
                                            class="flex-1 text-center py-2 rounded-xl bg-[#2563eb] text-white text-sm font-semibold hover:bg-[#1d4ed8] transition">
                                            View
                                        </a>

                                        @if (!$collocation->isCancelled())
                                            <a href="{{ route('expense.index', $collocation) }}"
                                                class="flex-1 text-center py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                                                Expenses
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>



                {{-- ================= RIGHT : WHO OWES WHO ================= --}}
                {{-- Until we add the axpnse and to be divided evenly  --}}
                <div>
                    <h3 class="text-xl font-extrabold text-[#1b364b] mb-6">
                        Who Owes Who
                    </h3>

                    <div class="bg-white rounded-3xl border border-[#e2e8f0] shadow-sm divide-y">

                        {{-- @forelse($balances as $balance)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50 transition">

                                <div class="text-sm">
                                    <p class="font-semibold text-slate-800">
                                        {{ $balance->debtor->name }}
                                    </p>
                                    <p class="text-slate-500 text-xs">
                                        owes {{ $balance->creditor->name }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-red-500">
                                        {{ number_format($balance->amount, 2) }} €
                                    </p>
                                </div>

                            </div>

                        @empty

                            <div class="p-10 text-center text-slate-500 text-sm">
                                No debts 🎉 Everything is settled.
                            </div>
                        @endforelse --}}

                    </div>
                </div>

            </section>

            <section>
                <h3 class="text-lg font-extrabold text-[#1b364b] mb-5">Recent Expenses</h3>

                @if ($recentExpenses->isEmpty())
                    <div class="bg-white rounded-2xl border border-[#dae2ec] p-8 text-center">
                        <p class="text-[#657e9a] text-sm">No expenses recorded yet.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm overflow-hidden">
                        <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-[#f4f9ff] border-b border-[#dae2ec]">
                            <div class="col-span-4 text-xs font-bold text-[#657e9a] uppercase tracking-wider">
                                Description</div>
                            <div class="col-span-3 text-xs font-bold text-[#657e9a] uppercase tracking-wider">
                                Collocation</div>
                            <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Category
                            </div>
                            <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Date</div>
                            <div
                                class="col-span-1 text-xs font-bold text-[#657e9a] uppercase tracking-wider text-right">
                                Amount</div>
                        </div>
                        <ul class="divide-y divide-[#f0f4f8]">
                            @foreach ($recentExpenses as $expense)
                                <li
                                    class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#f9fbff] transition-colors">
                                    <div class="col-span-4 text-sm font-semibold text-[#142c3e] truncate">
                                        {{ $expense->description ?? '—' }}
                                    </div>
                                    <div class="col-span-3 text-sm text-[#657e9a] truncate">
                                        {{ $expense->collocation->name }}</div>
                                    <div class="col-span-2">
                                        <span
                                            class="text-xs font-semibold bg-[#f4f9ff] text-[#2563eb] border border-[#2563eb]/20 px-2.5 py-1 rounded-full">
                                            {{ $expense->category->name }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-sm text-[#657e9a]">
                                        {{ $expense->expense_date->format('d M Y') }}</div>
                                    <div class="col-span-1 text-right text-sm font-bold text-[#142c3e]">
                                        €{{ number_format($expense->amount, 2) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
