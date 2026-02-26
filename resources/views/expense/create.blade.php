<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('expense.index', $collocation) }}"
                class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                ← Expenses
            </a>
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Add Expense</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 sm:p-10 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-blue-200/20 blur-[80px] rounded-full pointer-events-none">
                </div>

                <div class="relative z-10">
                    <form method="POST" action="{{ route('expense.store', $collocation) }}">
                        @csrf
                        {{-- Title --}}
                        <div class="mb-5">
                            <x-input-label for="title" :value="__('Title')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="title" name="title" type="text"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('title')" placeholder="Enter a title" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        {{-- Amount --}}
                        <div class="mb-5">
                            <x-input-label for="amount" :value="__('Amount (DH)')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('amount')" placeholder="0.00" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        </div>

                        {{-- Category --}}

                        <div class="mb-5">
                            <x-input-label for="category_id" :value="__('Category')" class="font-bold text-[#1b364b]" />

                           <input type="text" id="new_category" name="new_category"
                                    placeholder="Enter a category…" required
                                    class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb] text-[#142c3e] py-3 px-4"
                                    value="{{ old('new_category') }}">
                                <input type="hidden" name="category_id" value="new">
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                            <x-input-error class="mt-2" :messages="$errors->get('new_category')" />
                        </div>

                        {{-- Date --}}
                        <div class="mb-5">
                            <x-input-label for="expense_date" :value="__('Date')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="expense_date" name="expense_date" type="date"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('expense_date', now()->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
                        </div>

                        {{-- Description --}}
                        <div class="mb-8">
                            <x-input-label for="description" :value="__('Description (optional)')" class="font-bold text-[#1b364b]" />
                            <textarea id="description" name="description" rows="3"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb] text-[#142c3e] resize-none"
                                placeholder="What was this expense for?">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn-vibrant sm:flex-1">Save Expense</button>
                            <a href="{{ route('expense.index', $collocation) }}"
                                class="sm:flex-1 block text-center py-4 text-base border border-[#d3e0f0] rounded-full font-bold text-[#2563eb] bg-white hover:bg-[#f2f9ff] transition-all">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
