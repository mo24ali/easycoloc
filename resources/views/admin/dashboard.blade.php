<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black tracking-tight text-[#142c3e]">Admin Dashboard</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('status'))
                <div class="px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-green-600 font-semibold text-sm mb-6">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-600 font-semibold text-sm mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Total Users</h3>
                    <p class="text-3xl font-bold text-[#2563eb]">{{ $totalUsers }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Banned Users</h3>
                    <p class="text-3xl font-bold text-red-500">{{ $totalBannedUsers }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Admins</h3>
                    <p class="text-3xl font-bold text-[#2563eb]">{{ $totalAdmins }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Total Collocations</h3>
                    <p class="text-3xl font-bold text-[#2563eb]">{{ $totalCollocations }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Active Members</h3>
                    <p class="text-3xl font-bold text-[#2563eb]">{{ $totalMembers }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#657e9a] mb-2">Total Expenses</h3>
                    <p class="text-3xl font-bold text-[#2563eb]">MAD{{ number_format($totalExpenses, 2) }}</p>
                </div>
            </div>

            {{-- Users Management --}}
            <div class="bg-white rounded-2xl border border-[#dae2ec] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#e0e5eb]">
                    <h3 class="text-lg font-extrabold text-[#1b364b]">Users Management</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#f9fafb] border-b border-[#e0e5eb]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#657e9a] uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#657e9a] uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#657e9a] uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#657e9a] uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#657e9a] uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e0e5eb]">
                            @forelse($users as $user)
                                <tr class="hover:bg-[#f9fafb] transition-colors">
                                    <td class="px-6 py-4 text-sm font-semibold text-[#142c3e]">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#657e9a]">{{ $user->email }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                            @if($user->isAdmin())
                                                bg-purple-50 text-purple-600 border border-purple-200
                                            @elseif($user->isOwner())
                                                bg-blue-50 text-blue-600 border border-blue-200
                                            @elseif($user->isMember())
                                                bg-green-50 text-green-600 border border-green-200
                                            @else
                                                bg-gray-50 text-gray-600 border border-gray-200
                                            @endif
                                        ">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($user->is_banned)
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200">
                                                Banned
                                            </span>
                                        @else
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-600 border border-green-200">
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            @if(!$user->isAdmin())
                                                @if($user->is_banned)
                                                    <form method="POST" action="{{ route('admin.unban', $user) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 text-xs font-bold text-green-600 bg-green-50 border border-green-200 rounded hover:bg-green-100 transition-colors">
                                                            Unban
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.ban', $user) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 text-xs font-bold text-red-600 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition-colors">
                                                            Ban
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('admin.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1 text-xs font-bold text-orange-600 bg-orange-50 border border-orange-200 rounded hover:bg-orange-100 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-[#657e9a]">Admin (protected)</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-[#657e9a] text-sm">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-[#e0e5eb]">
                    {{ $users->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
