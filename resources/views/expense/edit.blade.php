<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('expense.index', $expense->collocation_id) }}"
                class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                ← Expenses
            </a>
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Edit Expense</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 sm:p-10 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-blue-200/20 blur-[80px] rounded-full pointer-events-none">
                </div>

                <div class="relative z-10">
                    <form method="POST" action="{{ route('expense.update', $expense) }}">
                        @csrf
                        @method('PUT')

                        {{-- Title --}}
                        <div class="mb-5">
                            <x-input-label for="title" :value="__('Title')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="title" name="title" type="text"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('title', $expense->title)" placeholder="Enter a title" required />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        {{-- Amount --}}
                        <div class="mb-5">
                            <x-input-label for="amount" :value="__('Amount (€)')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('amount', $expense->amount)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        </div>

                        {{-- Category --}}
                        <div class="mb-5">
                            <x-input-label for="category_id" :value="__('Category')" class="font-bold text-[#1b364b]" />
                            <select id="category_id" name="category_id" required
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb] text-[#142c3e] py-3 px-4">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $expense->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        {{-- Date --}}
                        <div class="mb-5">
                            <x-input-label for="expense_date" :value="__('Date')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="expense_date" name="expense_date" type="date"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('expense_date', $expense->expense_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
                        </div>

                        {{-- Description --}}
                        <div class="mb-8">
                            <x-input-label for="description" :value="__('Description (optional)')"
                                class="font-bold text-[#1b364b]" />
                            <textarea id="description" name="description" rows="3"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb] text-[#142c3e] resize-none">{{ old('description', $expense->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn-vibrant sm:flex-1">Save Changes</button>
                            <a href="{{ route('expense.index', $expense->collocation_id) }}"
                                class="sm:flex-1 block text-center py-4 text-base border border-[#d3e0f0] rounded-full font-bold text-[#2563eb] bg-white hover:bg-[#f2f9ff] transition-all">
                                Discard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
