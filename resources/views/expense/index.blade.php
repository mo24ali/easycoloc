<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('collocation.show', $collocation) }}"
                    class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← {{ $collocation->name }}
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Expenses</h2>
            </div>
            <a href="{{ route('expense.create', $collocation) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all">
                + Add Expense
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Math Map Statistics Output --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category Velocity -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm">
                    <h3 class="text-sm font-extrabold text-[#657e9a] mb-4 uppercase tracking-wider">Category Velocity
                    </h3>
                    @if($categoryStats->isEmpty())
                        <p class="text-xs text-[#a3b8cc]">No data available.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($categoryStats as $name => $amount)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-[#142c3e]">{{ $name }}</span>
                                    <span class="text-sm font-bold text-[#2563eb]">€{{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Monthly Velocity -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-sm">
                    <h3 class="text-sm font-extrabold text-[#657e9a] mb-4 uppercase tracking-wider">Monthly Velocity
                    </h3>
                    @if($monthlyStats->isEmpty())
                        <p class="text-xs text-[#a3b8cc]">No data available.</p>
                    @else
                        <div class="space-y-3 max-h-[150px] overflow-y-auto pr-2">
                            @foreach($monthlyStats as $name => $amount)
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-semibold text-[#142c3e]">{{ \Carbon\Carbon::parse($name)->format('M. Y') }}</span>
                                    <span class="text-sm font-bold text-[#2563eb]">€{{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Category & Month Filters --}}
            <div class="flex flex-col gap-3 p-5 bg-[#f8fafc] rounded-2xl border border-[#e2e8f0]">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-semibold text-[#4b6379] w-20">Category:</span>
                    <a href="{{ route('expense.index', [$collocation, 'month' => request('month')]) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ !$categoryId ? 'bg-[#2563eb] text-white' : 'bg-white border border-[#dae2ec] text-[#4b6379] hover:bg-[#f4f9ff]' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('expense.index', [$collocation, 'category' => $cat->id, 'month' => request('month')]) }}"
                            class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ $categoryId == $cat->id ? 'bg-[#2563eb] text-white' : 'bg-white border border-[#dae2ec] text-[#4b6379] hover:bg-[#f4f9ff]' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                @if($monthlyStats->isNotEmpty())
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm font-semibold text-[#4b6379] w-20">Month:</span>
                        <a href="{{ route('expense.index', [$collocation, 'category' => request('category')]) }}"
                            class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ !isset($month) || !$month ? 'bg-[#2563eb] text-white' : 'bg-white border border-[#dae2ec] text-[#4b6379] hover:bg-[#f4f9ff]' }}">
                            All Time
                        </a>
                        @foreach($monthlyStats->keys() as $mKey)
                            <a href="{{ route('expense.index', [$collocation, 'month' => $mKey, 'category' => request('category')]) }}"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors {{ (isset($month) && $month === $mKey) ? 'bg-[#2563eb] text-white' : 'bg-white border border-[#dae2ec] text-[#4b6379] hover:bg-[#f4f9ff]' }}">
                                {{ \Carbon\Carbon::parse($mKey)->format('M. Y') }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Expense list --}}
            @if($expenses->isEmpty())
                <div class="bg-white rounded-2xl border border-[#dae2ec] p-12 text-center">
                    <p class="text-[#657e9a] text-sm mb-4">No expenses yet.</p>
                    <a href="{{ route('expense.create', $collocation) }}"
                        class="inline-block px-6 py-3 bg-[#2563eb] text-white font-bold text-sm rounded-full hover:bg-[#1a4ac4] transition-all">
                        Add your first expense
                    </a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm overflow-hidden">
                    {{-- Table head --}}
                    <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-[#f4f9ff] border-b border-[#dae2ec]">
                        <div class="col-span-4 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Description</div>
                        <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Member</div>
                        <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Category</div>
                        <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Date</div>
                        <div class="col-span-1 text-xs font-bold text-[#657e9a] uppercase tracking-wider text-right">Amount
                        </div>
                        <div class="col-span-1"></div>
                    </div>

                    <ul class="divide-y divide-[#f0f4f8]">
                        @foreach($expenses as $expense)
                            <li class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#f9fbff] transition-colors">
                                <div class="col-span-4">
                                    <p class="text-sm font-semibold text-[#142c3e] truncate">{{ $expense->title }}</p>
                                    @if($expense->description)
                                        <p class="text-xs text-[#657e9a] truncate">{{ $expense->description }}</p>
                                    @endif
                                </div>
                                <div class="col-span-2 text-sm text-[#657e9a] truncate">{{ $expense->member->name }}</div>
                                <div class="col-span-2">
                                    <span
                                        class="text-xs font-semibold bg-[#f4f9ff] text-[#2563eb] border border-[#2563eb]/20 px-2.5 py-1 rounded-full">
                                        {{ $expense->category->name }}
                                    </span>
                                </div>
                                <div class="col-span-2 text-sm text-[#657e9a]">{{ $expense->expense_date->format('d M Y') }}
                                </div>
                                <div class="col-span-1 text-right text-sm font-bold text-[#142c3e]">
                                    €{{ number_format($expense->amount, 2) }}
                                </div>
                                <div class="col-span-1 flex justify-end gap-2">
                                    @can('update', $expense)
                                        <a href="{{ route('expense.edit', $expense) }}"
                                            class="text-xs text-[#2563eb] hover:underline font-bold">Edit</a>
                                    @endcan
                                    @can('delete', $expense)
                                        <form method="POST" action="{{ route('expense.destroy', $expense) }}"
                                            onsubmit="return confirm('Delete this expense?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-500 hover:underline font-bold">Del</button>
                                        </form>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-6">{{ $expenses->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>