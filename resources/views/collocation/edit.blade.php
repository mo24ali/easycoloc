<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('collocation.show', $collocation) }}"
                class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                ← {{ $collocation->name }}
            </a>
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                Edit Collocation
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 sm:p-10 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-blue-200/30 blur-[80px] rounded-full pointer-events-none">
                </div>

                <div class="relative z-10">
                    <form method="POST" action="{{ route('collocation.update', $collocation) }}">
                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div class="mb-6">
                            <x-input-label for="name" :value="__('Collocation Name')"
                                class="text-[#1b364b] font-bold" />
                            <x-text-input id="name" name="name" type="text"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('name', $collocation->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        {{-- Status --}}
                        <div class="mb-8">
                            <x-input-label for="status" :value="__('Status')" class="text-[#1b364b] font-bold" />
                            <select id="status" name="status"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb] text-[#142c3e] font-medium py-3 px-4">
                                <option value="active" @selected(old('status', $collocation->status) === 'active')>Active
                                </option>
                                <option value="inactive" @selected(old('status', $collocation->status) === 'inactive')>
                                    Inactive</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn-vibrant sm:flex-1">
                                Save Changes
                            </button>
                            <a href="{{ route('collocation.show', $collocation) }}"
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