<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('collocation.show', $collocation) }}"
                    class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← {{ $collocation->name }}
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                    Members
                    <span class="text-base font-semibold text-[#657e9a] ml-2">({{ $members->total() }})</span>
                </h2>
            </div>
            @can('update', $collocation)
                <a href="{{ route('invitation.create', $collocation) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all">
                    + Invite Member
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($members->isEmpty())
                <div class="text-center py-20">
                    <p class="text-xl font-bold text-[#1b364b] mb-2">No members yet</p>
                    <p class="text-sm text-[#657e9a]">Invite people to join this collocation.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm overflow-hidden">

                    {{-- Table header --}}
                    <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-[#f4f9ff] border-b border-[#dae2ec]">
                        <div class="col-span-5 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Member</div>
                        <div class="col-span-3 text-xs font-bold text-[#657e9a] uppercase tracking-wider">Role</div>
                        <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider text-center">
                            Reputation</div>
                        <div class="col-span-2 text-xs font-bold text-[#657e9a] uppercase tracking-wider text-right">Actions
                        </div>
                    </div>

                    <ul class="divide-y divide-[#f0f4f8]">
                        @foreach ($members as $member)
                            <li class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#f9fbff] transition-colors">

                                {{-- Avatar + name + email --}}
                                <div class="col-span-5 flex items-center gap-4 min-w-0">
                                    <div
                                        class="w-10 h-10 bg-[#2563eb]/10 rounded-full flex items-center justify-center shrink-0">
                                        <span class="text-[#2563eb] text-sm font-bold">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[#142c3e] truncate">{{ $member->name }}</p>
                                        <p class="text-xs text-[#657e9a] truncate">{{ $member->email }}</p>
                                    </div>
                                </div>

                                {{-- Role badge (from pivot) --}}
                                <div class="col-span-3">
                                    @php $pivotRole = $member->pivot->role ?? 'member'; @endphp
                                    <span
                                        class="{{ $pivotRole === 'owner' ? 'bg-[#2563eb] text-white' : 'bg-[#f4f9ff] text-[#2563eb] border border-[#2563eb]/20' }} text-xs font-semibold px-3 py-1 rounded-full capitalize">
                                        {{ $pivotRole }}
                                    </span>
                                </div>

                                {{-- Reputation score --}}
                                <div class="col-span-2 text-center">
                                    <span class="text-sm font-semibold text-[#4b6379]">
                                        ⭐ {{ $member->reputation_score ?? 0 }}
                                    </span>
                                </div>

                                {{-- Remove member / Pass ownership action (owner only, can't remove self) --}}
                                <div class="col-span-2 flex justify-end">
                                    @can('update', $collocation)
                                        @if($member->id !== $collocation->owner_id)
                                            <div class="flex items-center gap-3">
                                                <form method="POST"
                                                    action="{{ route('collocation.passOwnership', [$collocation, $member]) }}"
                                                    onsubmit="return confirm('Pass ownership of this collocation to {{ $member->name }}?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-xs font-bold text-[#2563eb] hover:text-[#1e4db7] hover:underline transition-colors shrink-0">
                                                        Pass Ownership
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('collocation.removeMember', [$collocation, $member]) }}"
                                                    onsubmit="return confirm('Remove {{ $member->name }} from this collocation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-xs font-bold text-red-500 hover:text-red-700 hover:underline transition-colors shrink-0">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-[#657e9a]">Owner</span>
                                        @endif
                                    @endcan
                                </div>

                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $members->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>