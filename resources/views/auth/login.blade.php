```
<x-guest-layout>
    <div class="fixed inset-0 bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50 overflow-auto">
        <div class="min-h-full flex items-center justify-center p-4 py-8">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-white/80 shadow border">
                        <img src="{{ asset('images/logo_englishedugame.png') }}" alt="EnglishEdu Logo" style="width: 144px !important; height: 144px !important;" class="rounded-full object-cover">
                    </div>
                    <h1 class="mt-4 text-2xl font-extrabold text-sky-900">Welcome To EnglishEdu!</h1>
                    <p class="text-sm text-sky-700/80">Practice your English skills with fun games!</p>
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
                {{ __('Forgot password?') }}
            </a>
        @endif
    </div>

    <div class="relative mt-1">
        <x-text-input
            id="password"
            class="block w-full rounded-xl border-sky-200 focus:border-sky-400 focus:ring-sky-300 pr-16"
            type="password"
            name="password"
            required
            autocomplete="current-password"
        />

        <!-- Toggle Button INSIDE the input, aligned to right -->
        <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-sky-600 hover:text-sky-800 hover:bg-sky-50 font-medium rounded-r-xl cursor-pointer"
            onclick="togglePasswordVisibility('password')"
            aria-label="Toggle password visibility"
        >
            <span id="password-toggle-text">Show</span>
        </button>
    </div>

    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const input = document.getElementById(fieldId);
        const toggleText = document.getElementById('password-toggle-text');
        
        if (input.type === 'password') {
            input.type = 'text';
            toggleText.textContent = 'Hide';
        } else {
            input.type = 'password';
            toggleText.textContent = 'Show';
        }
    }
</script>

                        <!-- Remember Me -->
                        <label for="remember_me" class="inline-flex items-center select-none">
                            <input id="remember_me" type="checkbox" class="rounded border-sky-300 text-sky-600 focus:ring-sky-400" name="remember">
                            <span class="ms-2 text-sm text-sky-900/80">Remember me</span>
                        </label>

                        <x-primary-button class="w-full justify-center rounded-xl bg-sky-500 hover:bg-sky-600 focus:ring-sky-300">
                            <span class="text-white">Login ✨</span>
                        </x-primary-button>
                    </form>

                    <p class="mt-4 text-center text-sm text-sky-900/80">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-900 underline">Register now</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

```