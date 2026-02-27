<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('payment.index', $collocation) }}" class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← {{ $collocation->name }} Payments
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Record Payment</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <p class="text-sm font-bold text-red-700 mb-2">Please fix the following errors:</p>
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-600">• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('payment.store', $collocation) }}" class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-8">
                @csrf

                <div class="space-y-6">

                    {{-- Receiver --}}
                    <div>
                        <label for="receiver_id" class="block text-sm font-bold text-[#142c3e] mb-2">
                            Who did you pay? *
                        </label>
                        <select name="receiver_id" id="receiver_id" required
                                class="w-full px-4 py-3 border border-[#dae2ec] rounded-lg text-sm focus:outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 @error('receiver_id') border-red-500 @enderror">
                            <option value="">-- Select receiver --</option>
                            @foreach($collocation->members as $member)
                                @if($member->id !== auth()->id())
                                    <option value="{{ $member->id }}" {{ old('receiver_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('receiver_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-bold text-[#142c3e] mb-2">
                            Amount (€) *
                        </label>
                        <div class="relative">
                            <input type="number"
                                   name="amount"
                                   id="amount"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0.01"
                                   value="{{ old('amount') }}"
                                   required
                                   class="w-full px-4 py-3 border border-[#dae2ec] rounded-lg text-sm focus:outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 @error('amount') border-red-500 @enderror">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-[#657e9a]">
                            Enter the exact amount you paid
                        </p>
                    </div>

                    {{-- Info box --}}
                    <div class="bg-[#f4f9ff] border border-[#2563eb]/20 rounded-xl p-4">
                        <p class="text-sm text-[#2563eb] font-bold">ℹ️ Payment Flow</p>
                        <ul class="mt-2 space-y-1 text-xs text-[#657e9a]">
                            <li>1️⃣ You create the payment record</li>
                            <li>2️⃣ Receiver confirms they received the payment</li>
                            <li>3️⃣ You mark it as paid</li>
                            <li>4️⃣ Payment is completed</li>
                        </ul>
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 mt-8">
                    <a href="{{ route('payment.index', $collocation) }}"
                       class="flex-1 px-6 py-3 text-sm font-bold text-[#657e9a] border border-[#dae2ec] rounded-full hover:bg-[#f4f9ff] transition-colors text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 px-6 py-3 text-sm font-bold text-white bg-[#2563eb] rounded-full hover:bg-[#1a4ac4] transition-colors">
                        Record Payment
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
