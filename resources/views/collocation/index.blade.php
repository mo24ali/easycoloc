<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">
                My Collocations
            </h2>
            @if(Auth::user()->isOwner())
                <a href="{{ route('collocation.create') }}" class="btn-vibrant !w-auto !px-6 !py-3 !text-base">
                    + New Collocation
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash success message --}}
            @if(session('status'))
                <div class="mb-6 px-5 py-4 bg-[#f4f9ff] border border-[#2563eb]/20 rounded-2xl text-[#2563eb] font-semibold text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($collocations->isEmpty())
                <div class="text-center py-24">
                    <div class="w-20 h-20 bg-[#f4f9ff] rounded-3xl flex items-center justify-center mx-auto mb-6 border border-[#dae2ec]">
                        <span class="text-[#2563eb] text-4xl font-black italic">E</span>
                    </div>
                    <p class="text-xl font-bold text-[#1b364b] mb-2">No collocations yet.</p>
                    <p class="text-[#657e9a] text-sm">
                        @if(Auth::user()->isOwner())
                            Create your first collocation to get started.
                        @else
                            You haven't joined any collocation yet. Wait for an invitation!
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($collocations as $collocation)
                        <div class="bg-white rounded-2xl shadow-sm border border-[#dae2ec] p-6 flex flex-col gap-4 hover:shadow-md transition-shadow">

                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-lg font-bold text-[#142c3e] leading-tight">
                                    {{ $collocation->name }}
                                </h3>
                                @if($collocation->isCancelled())
                                    <span class="shrink-0 bg-red-50 text-red-500 text-xs font-semibold px-3 py-1 rounded-full border border-red-200">
                                        Cancelled
                                    </span>
                                @elseif($collocation->status === 'active')
                                    <span class="shrink-0 bg-[#f4f9ff] text-[#2563eb] text-xs font-semibold px-3 py-1 rounded-full border border-[#2563eb]/20">
                                        Active
                                    </span>
                                @else
                                    <span class="shrink-0 bg-gray-50 text-gray-500 text-xs font-semibold px-3 py-1 rounded-full border border-gray-200">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            {{-- Meta --}}
                            <p class="text-sm text-[#657e9a]">
                                <span class="font-semibold text-[#4b6379]">{{ $collocation->members_count }}</span>
                                {{ Str::plural('member', $collocation->members_count) }}
                                &nbsp;·&nbsp;
                                Created {{ $collocation->created_at->diffForHumans() }}
                            </p>

                            {{-- Actions --}}
                            <div class="mt-auto flex gap-3">
                                <a href="{{ route('collocation.show', $collocation) }}"
                                   class="flex-1 text-center py-2.5 text-sm font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-colors">
                                    View Details
                                </a>
                                @can('update', $collocation)
                                    <a href="{{ route('collocation.edit', $collocation) }}"
                                       class="flex-1 text-center py-2.5 text-sm font-bold border border-[#d3e0f0] text-[#2563eb] bg-white rounded-full hover:bg-[#f2f9ff] transition-colors">
                                        Edit
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $collocations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
