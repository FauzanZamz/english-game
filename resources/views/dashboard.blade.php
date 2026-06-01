<x-app-layout>
  <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-amber-50 to-blue-50">
    <div class="max-w-3xl mx-auto px-6 py-10">
      <div class="rounded-3xl bg-white/90 backdrop-blur shadow-lg border p-8 relative overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-pink-200/60 animate-pulse-slow"></div>
        <div class="absolute -bottom-10 -left-10 w-52 h-52 rounded-full bg-sky-200/50 animate-pulse-slow"></div>

        <div class="relative">
          <h1 class="text-3xl font-extrabold text-sky-900 animate-fade-in">Selamat datang! 🌈</h1>
          <p class="mt-1 text-sky-800/80 animate-fade-in animation-delay-200">Pilih permainan untuk mulai belajar dengan cara yang menyenangkan.</p>

          <!-- Single Column Layout -->
<div class="mt-8 space-y-6">
    <!-- Spelling Bee Card -->
    <a href="{{ route('spelling.index') }}"
        class="group rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 border shadow hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 p-6 block">
        <div class="flex items-start gap-4">
            <div class="text-4xl transition-transform duration-300 group-hover:scale-110">🎤</div>
            <div>
                <h2 class="text-xl font-bold text-amber-700 group-hover:text-amber-800 transition-colors">Spelling Bee</h2>
                <p class="text-amber-900/70 text-sm mt-1">Dengar, eja, jawab! Dukungan TTS & voice input.</p>
            </div>
        </div>
        <div class="mt-4">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs border transition-all duration-200 group-hover:bg-amber-200">
                Level Beginner & Expert
            </span>
        </div>
    </a>

    <!-- Crossword Card -->
    <a href="{{ route('crossword.index') }}"
        class="group rounded-2xl bg-gradient-to-br from-blue-50 to-sky-50 border shadow hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 p-6 block">
        <div class="flex items-start gap-4">
            <div class="text-4xl transition-transform duration-300 group-hover:scale-110">🧩</div>
            <div>
                <h2 class="text-xl font-bold text-sky-700 group-hover:text-sky-800 transition-colors">Crossword</h2>
                <p class="text-sky-900/70 text-sm mt-1">Susun kata bertema, grid otomatis, skor per huruf.</p>
            </div>
        </div>
        <div class="mt-4">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-sky-100 text-sky-700 text-xs border transition-all duration-200 group-hover:bg-sky-200">
                Grid 12×12 / 15×15
            </span>
        </div>
    </a>
</div>

<!-- Leaderboards -->
<div class="mt-8 flex flex-wrap gap-3">
    <a href="{{ route('leaderboard.spelling') }}"
        class="px-4 py-2 rounded-full bg-fuchsia-100 text-fuchsia-700 border hover:bg-fuchsia-200 transition-all duration-200 transform hover:scale-105">🏆 Leaderboard Spelling</a>
    <a href="{{ route('leaderboard.crossword') }}"
        class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 border hover:bg-emerald-200 transition-all duration-200 transform hover:scale-105">🏆 Leaderboard Crossword</a>
</div>
  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
      animation: fade-in 0.6s ease-out forwards;
    }
    .animation-delay-200 {
      animation-delay: 0.2s;
    }
    @keyframes pulse-slow {
      0%, 100% { opacity: 0.6; }
      50% { opacity: 0.3; }
    }
    .animate-pulse-slow {
      animation: pulse-slow 6s infinite ease-in-out;
    }
  </style>
</x-app-layout>
