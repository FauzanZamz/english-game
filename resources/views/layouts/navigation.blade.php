<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm bg-gradient-to-r from-pink-50 to-blue-50">
                    <span class="text-xl">🪄</span>
                    <span class="font-bold text-sky-900">EnglishEdu</span>
                </a>
                @auth
                <a href="{{ route('spelling.index') }}" class="text-sky-800 hover:text-sky-900 text-sm">Spelling Bee</a>
                <a href="{{ route('crossword.index') }}" class="text-sky-800 hover:text-sky-900 text-sm">Crossword</a>
                <a href="{{ route('leaderboard.spelling') }}" class="text-sky-800 hover:text-sky-900 text-sm">Leaderboard</a>
                @endauth
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border rounded-xl bg-white hover:bg-sky-50 text-sm">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path d="M5.5 7l4.5 5 4.5-5z"/></svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md hover:bg-sky-50">
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
            <x-responsive-nav-link :href="route('spelling.index')">Spelling Bee</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('crossword.index')">Crossword</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard.spelling')">Leaderboard</x-responsive-nav-link>
            @endauth
        </div>
        @auth
        <div class="pt-4 pb-1 border-t">
            <div class="px-4">
                <div class="font-medium text-base">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
