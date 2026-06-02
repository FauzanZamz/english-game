<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm bg-gradient-to-r from-pink-50 to-blue-50 hover:shadow-md transition">
                    <span class="text-xl">🪄</span>
                    <span class="font-bold text-sky-900">EnglishEdu</span>
                </a>
                @auth
                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('spelling.index') }}" class="text-sky-800 hover:text-sky-900 text-sm font-medium px-3 py-2 rounded-md hover:bg-sky-50 transition">Spelling Bee</a>
                    <a href="{{ route('crossword.index') }}" class="text-sky-800 hover:text-sky-900 text-sm font-medium px-3 py-2 rounded-md hover:bg-sky-50 transition">Crossword</a>
                    <a href="{{ route('leaderboard.spelling') }}" class="text-sky-800 hover:text-sky-900 text-sm font-medium px-3 py-2 rounded-md hover:bg-sky-50 transition">Leaderboard Spelling Bee</a>
                    <a href="{{ route('leaderboard.crossword') }}" class="text-sky-800 hover:text-sky-900 text-sm font-medium px-3 py-2 rounded-md hover:bg-sky-50 transition">Leaderboard Crossword</a>
                </div>
                @endauth
            </div>

<!-- Settings Dropdown -->
<div class="hidden sm:flex sm:items-center sm:ms-6 relative z-[9999]">
    @auth
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-3 py-2 border rounded-xl bg-white hover:bg-sky-50 text-sm font-medium text-sky-800 transition-all duration-200 group ">
                <span>{{ Auth::user()->name }}</span>
                <svg class="fill-current h-4 w-4 ms-1 group-hover:rotate-180 transition-transform" viewBox="0 0 20 20"><path d="M5.5 7l4.5 5 4.5-5z"/></svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="py-1 space-y-1">
                <x-dropdown-link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-sky-800 hover:bg-sky-50 hover:text-sky-900 transition">
                    Profile
                </x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </div>
        </x-slot>
    </x-dropdown>
    @endauth
</div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-sky-800 hover:bg-sky-50 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-responsive-nav-link :href="route('spelling.index')" class="text-sky-800 hover:text-sky-900">Spelling Bee</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('crossword.index')" class="text-sky-800 hover:text-sky-900">Crossword</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard.spelling')" class="text-sky-800 hover:text-sky-900">Leaderboard Spelling Bee</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard.crossword')" class="text-sky-800 hover:text-sky-900">Leaderboard Crossword</x-responsive-nav-link>
            @endauth
        </div>
        @auth
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-sky-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-sky-800 hover:text-sky-900">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="text-red-600 hover:text-red-700">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
