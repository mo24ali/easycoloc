<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs text-[#657e9a] font-semibold mb-0.5">Welcome back</p>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                    {{ Auth::user()->name }}
                    <span class="text-base font-semibold text-[#657e9a] capitalize ml-2">({{ Auth::user()->role }})</span>
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                @if(Auth::user()->isOwner())
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

            {{-- ═══ COLLOCATIONS grid ═══════════════════════════════════════════ --}}
            <section>
                <h3 class="text-lg font-extrabold text-[#1b364b] mb-5">
                    @if(Auth::user()->isOwner()) My Collocations @else My Colocation @endif
                    <span class="text-sm font-normal text-[#657e9a] ml-1">({{ $collocations->count() }})</span>
                </h3>

                @if($collocations->isEmpty())
                    <div class="bg-white rounded-2xl border border-[#dae2ec] p-10 text-center">
                        <p class="text-[#657e9a] text-sm">
                            @if(Auth::user()->isOwner())
                                You haven't created any collocation yet.
                                <a href="{{ route('collocation.create') }}" class="text-[#2563eb] font-bold hover:underline">Create one →</a>
                            @else
                                You haven't joined any collocation yet. Ask your owner for an invite link!
                            @endif
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($collocations as $collocation)
                            <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="text-base font-bold text-[#142c3e] leading-tight group-hover:text-[#2563eb] transition-colors">
                                        {{ $collocation->name }}
                                    </h4>
                                    @if($collocation->isCancelled())
                                        <span class="shrink-0 bg-red-50 text-red-500 text-xs font-bold px-2.5 py-1 rounded-full border border-red-200">Cancelled</span>
                                    @else
                                        <span class="shrink-0 bg-[#f4f9ff] text-[#2563eb] text-xs font-bold px-2.5 py-1 rounded-full border border-[#2563eb]/20">Active</span>
                                    @endif
                                </div>

                                <div class="text-sm text-[#657e9a] flex items-center gap-4">
                                    <span>👥 {{ $collocation->members_count }} {{ Str::plural('member', $collocation->members_count) }}</span>
                                    <span>📅 {{ $collocation->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="mt-auto flex gap-2">
                                    <a href="{{ route('collocation.show', $collocation) }}"
                                       class="flex-1 text-center py-2 text-sm font-bold bg-[#2563eb] text-white rounded-full hover:bg-[#1a4ac4] transition-colors">
                                        View
                                    </a>
                                    @if(!$collocation->isCancelled())
                                        <a href="{{ route('expense.index', $collocation) }}"
                                           class="flex-1 text-center py-2 text-sm font-bold border border-[#dae2ec] text-[#4b6379] rounded-full hover:bg-[#f4f9ff] transition-colors">
                                            Expenses
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ═══ RECENT EXPENSES ════════════════════════════════════════════ --}}
            <section>
                <h3 class="text-lg font-extrabold text-[#1b364b] mb-5">Recent Expenses</h3>

                @if($recentExpenses->isEmpty())
                    <div class="bg-white rounded-2xl border border-[#dae2ec] p-8 text-center">
                        <p class="text-[#657e9a] text-sm">No expenses recorded yet.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm overflow-hidden">
                        <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-[#f4f9ff] border-b border-[#dae2ec]">
                            <div class="col-span-4 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Description</div>
                            <div class="col-span-3 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Collocation</div>
                            <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Category</div>
                            <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Date</div>
                            <div class="col-span-1 text-xs font-bold text-[#657e9a] uppercase tracking-wider text-right">Amount</div>
                        </div>
                        <ul class="divide-y divide-[#f0f4f8]">
                            @foreach($recentExpenses as $expense)
                                <li class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#f9fbff] transition-colors">
                                    <div class="col-span-4 text-sm font-semibold text-[#142c3e] truncate">
                                        {{ $expense->description ?? '—' }}
                                    </div>
                                    <div class="col-span-3 text-sm text-[#657e9a] truncate">{{ $expense->collocation->name }}</div>
                                    <div class="col-span-2">
                                        <span class="text-xs font-semibold bg-[#f4f9ff] text-[#2563eb] border border-[#2563eb]/20 px-2.5 py-1 rounded-full">
                                            {{ $expense->category->name }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-sm text-[#657e9a]">{{ $expense->expense_date->format('d M Y') }}</div>
                                    <div class="col-span-1 text-right text-sm font-bold text-[#142c3e]">€{{ number_format($expense->amount, 2) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
