<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 shadow border">
                    <span class="text-xl">🎉</span>
                    <span class="font-semibold text-sky-700">EnglishEdu</span>
                </div>
                <h1 class="mt-4 text-2xl font-extrabold text-sky-900">Welcome back!</h1>
                <p class="text-sm text-sky-700/80">Belajar Inggris seru bareng Spelling Bee & Crossword</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border p-6">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full rounded-xl border-sky-200 focus:border-sky-400 focus:ring-sky-300"
                            type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" :value="__('Password')" />
                            @if (Route::has('password.request'))
                                <a class="text-sm text-sky-700 hover:text-sky-900 underline" href="{{ route('password.request') }}">
                                    {{ __('Lupa password?') }}
                                </a>
                            @endif
                        </div>

                        <x-text-input id="password"
                            class="block mt-1 w-full rounded-xl border-sky-200 focus:border-sky-400 focus:ring-sky-300"
                            type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <label for="remember_me" class="inline-flex items-center select-none">
                        <input id="remember_me" type="checkbox" class="rounded border-sky-300 text-sky-600 focus:ring-sky-400" name="remember">
                        <span class="ms-2 text-sm text-sky-900/80">Ingat saya</span>
                    </label>

                    <x-primary-button class="w-full justify-center rounded-xl bg-sky-500 hover:bg-sky-600 focus:ring-sky-300">
                        <span class="text-white">Masuk ✨</span>
                    </x-primary-button>
                </form>

                <p class="mt-4 text-center text-sm text-sky-900/80">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-900 underline">Daftar sekarang</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
