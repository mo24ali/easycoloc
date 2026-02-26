<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('collocation.index') }}"
                    class="text-xs text-[#657e9a] hover:text-[#2563eb] transition-colors mb-1 inline-block">
                    ← My Collocations
                </a>
                <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                    {{ $collocation->name }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                {{-- Status badge --}}
                @if($collocation->isCancelled())
                    <span
                        class="bg-red-50 text-red-500 text-xs font-bold px-4 py-1.5 rounded-full border border-red-200">Cancelled</span>
                @elseif($collocation->status === 'active')
                    <span
                        class="bg-[#f4f9ff] text-[#2563eb] text-xs font-bold px-4 py-1.5 rounded-full border border-[#2563eb]/20">Active</span>
                @else
                    <span
                        class="bg-gray-50 text-gray-500 text-xs font-bold px-4 py-1.5 rounded-full border border-gray-200">Inactive</span>
                @endif

                @can('update', $collocation)
                    <a href="{{ route('collocation.edit', $collocation) }}"
                        class="text-sm px-5 py-2 border border-[#d3e0f0] rounded-full font-bold text-[#2563eb] bg-white hover:bg-[#f2f9ff] transition-all">
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash status --}}
            @if(session('status'))
                <div
                    class="px-5 py-4 bg-[#f4f9ff] border border-[#2563eb]/20 rounded-2xl text-[#2563eb] font-semibold text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Info card --}}
            <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6 grid sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-[#657e9a] font-semibold uppercase tracking-wider mb-1">Owner</p>
                    <p class="text-base font-bold text-[#142c3e]">{{ $collocation->owner->name }}</p>
                    <p class="text-sm text-[#657e9a]">{{ $collocation->owner->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#657e9a] font-semibold uppercase tracking-wider mb-1">Created</p>
                    <p class="text-base font-bold text-[#142c3e]">{{ $collocation->created_at->format('d M Y') }}</p>
                    <p class="text-sm text-[#657e9a]">{{ $collocation->created_at->diffForHumans() }}</p>
                </div>
                @if($collocation->isCancelled())
                    <div class="sm:col-span-2">
                        <p class="text-xs text-red-400 font-semibold uppercase tracking-wider mb-1">Cancelled on</p>
                        <p class="text-base font-bold text-red-500">{{ $collocation->cancelled_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Members preview --}}
            <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-extrabold text-[#1b364b]">Members</h3>
                    <a href="{{ route('collocation.members', $collocation) }}"
                        class="text-sm font-bold text-[#2563eb] hover:underline">View all →</a>
                </div>

                @if($collocation->members->isEmpty())
                    <p class="text-sm text-[#657e9a]">No members yet. Invite people to join this collocation.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($collocation->members as $member)
                            <li class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-[#2563eb]/10 rounded-full flex items-center justify-center shrink-0">
                                    <span
                                        class="text-[#2563eb] text-sm font-bold">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-[#142c3e] truncate">{{ $member->name }}</p>
                                    <p class="text-xs text-[#657e9a] truncate">{{ $member->email }}</p>
                                </div>
                                <p class="text-xs text-[#657e9a] shrink-0">⭐ {{ $member->reputation_score ?? 0 }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Danger zone — Cancel --}}
            @can('cancel', $collocation)
                <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
                    <h3 class="text-base font-extrabold text-red-600 mb-2">Danger Zone</h3>
                    <p class="text-sm text-[#657e9a] mb-5">
                        Cancelling this collocation is irreversible. Members will no longer be able to add expenses.
                    </p>
                    <form method="POST" action="{{ route('collocation.cancel', $collocation) }}"
                        onsubmit="return confirm('Are you sure you want to cancel this collocation?');">
                        @csrf
                        <x-danger-button>
                            Cancel this Collocation
                        </x-danger-button>
                    </form>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>