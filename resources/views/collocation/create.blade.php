<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
            Create a New Collocation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Decorative card --}}
            <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 sm:p-10 relative overflow-hidden">

                {{-- Atmospheric blobs --}}
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-blue-200/30 blur-[80px] rounded-full pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-slate-200/30 blur-[80px] rounded-full pointer-events-none">
                </div>

                <div class="relative z-10">
                    {{-- Tagline --}}
                    <p
                        class="text-sm font-semibold text-[#4b6379] tracking-tight border-l-4 border-[#2563eb] pl-4 bg-[#f4f9ff] py-2 rounded-r-xl mb-8">
                        Once created, you can invite members and start tracking shared expenses.
                    </p>

                    <form method="POST" action="{{ route('collocation.store') }}">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-6">
                            <x-input-label for="name" :value="__('Collocation Name')"
                                class="text-[#1b364b] font-bold" />
                            <x-text-input id="name" name="name" type="text"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('name')" placeholder="e.g. Rue Victor Hugo — Apt. 4B" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" class="btn-vibrant sm:flex-1">
                                Create Collocation
                            </button>
                            <a href="{{ route('collocation.index') }}"
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