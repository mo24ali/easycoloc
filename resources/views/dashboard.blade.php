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
                <a href="{{ route('collocation.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all">
                    + New Collocation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Grid 2x2 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 grid-rows-2 gap-8">


                <div class="grid grid-cols-1 md:grid-cols-3 grid-rows-1 gap-8">
                    {{-- Top Left: Reputation Score --}}
                    <div
                        class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm flex flex-col justify-between">
                        <h3 class="text-xl font-extrabold text-[#1b364b] mb-4">My Reputation Score</h3>
                        <p class="text-3xl font-bold text-[#2563eb]">{{ $userReputation ?? 0 }}</p>
                        <p class="text-sm text-[#657e9a] mt-2">Your current reputation in all collocations</p>
                    </div>

                    {{-- Top Right: Global Expenses --}}
                    <div
                        class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm flex flex-col justify-between">
                        <h3 class="text-xl font-extrabold text-[#1b364b] mb-4">Global Expenses</h3>
                        <p class="text-3xl font-bold text-[#2563eb]">MAD{{ number_format($globalExpenses ?? 0, 2) }}</p>
                        <p class="text-sm text-[#657e9a] mt-2">Total expenses across all collocations</p>
                    </div>

                    <div
                        class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm flex flex-col justify-between">
                        <h3 class="text-xl font-extrabold text-[#1b364b] mb-4">My Total debt</h3>
                        <p class="text-3xl font-bold text-[#2563eb]">MAD{{ number_format($globalDebt ?? 0, 2) }}</p>
                        <p class="text-sm text-[#657e9a] mt-2">Total debt across all collocations</p>
                    </div>
                </div>

                {{-- Bottom Left: Recent Expenses --}}
                <div class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm">
                    <h3 class="text-xl font-extrabold text-[#1b364b] mb-4">Recent Expenses</h3>
                    @if ($recentExpenses->isEmpty())
                        <p class="text-[#657e9a] text-sm">No expenses recorded yet.</p>
                    @else
                        <ul class="divide-y divide-[#f0f4f8]">
                            @foreach ($recentExpenses as $expense)
                                <li class="py-2 flex justify-between items-center">
                                    <span class="text-sm text-[#142c3e]">{{ $expense->description ?? '—' }}</span>
                                    <span
                                        class="text-sm text-[#657e9a]">€{{ number_format($expense->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Bottom Right: Collocation Members --}}
                <div class="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-sm">
                    <h3 class="text-xl font-extrabold text-[#1b364b] mb-4">Collocation Members</h3>
                    @if ($collocations->isEmpty())
                        <p class="text-[#657e9a] text-sm">No collocations yet.</p>
                    @else
                        <ul class="divide-y divide-[#f0f4f8]">
                            @foreach ($collocations as $collocation)
                                @foreach ($collocation->members as $member)
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-sm text-[#142c3e]">{{ $member->name }}</span>
                                        <span class="text-xs text-[#657e9a]">{{ $collocation->name }}</span>
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
