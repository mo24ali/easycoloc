<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('collocation.show', $collocation) }}"
                class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                ← {{ $collocation->name }}
            </a>
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Invite a Member</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 sm:p-10 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-blue-200/20 blur-[80px] rounded-full pointer-events-none">
                </div>

                <div class="relative z-10">
                    <blockquote
                        class="border-l-4 border-[#2563eb] bg-[#f4f9ff] pl-4 py-2 rounded-r-xl text-sm font-semibold text-[#4b6379] mb-8">
                        An invitation link valid for <strong>14 days</strong> will be sent to the email address.
                    </blockquote>

                    <form method="POST" action="{{ route('invitation.store', $collocation) }}">
                        @csrf

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('Email address')" class="font-bold text-[#1b364b]" />
                            <x-text-input id="email" name="email" type="email"
                                class="mt-2 block w-full rounded-2xl border-[#dae2ec] focus:ring-[#2563eb] focus:border-[#2563eb]"
                                :value="old('email')" placeholder="roommate@example.com" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <button type="submit" class="btn-vibrant sm:flex-1">
                                Send Invitation ✉️
                            </button>
                            <a href="{{ route('collocation.show', $collocation) }}"
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