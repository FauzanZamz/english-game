<x-app-layout>
  <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-amber-50 to-blue-50">
    <div class="max-w-6xl mx-auto px-6 py-10">
      <div class="rounded-3xl bg-white/90 backdrop-blur shadow-lg border p-8 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-pink-200/60"></div>
        <div class="absolute -bottom-10 -left-10 w-52 h-52 rounded-full bg-sky-200/50"></div>

        <div class="relative">
          <h1 class="text-3xl font-extrabold text-sky-900">Selamat datang! 🌈</h1>
          <p class="mt-1 text-sky-800/80">Pilih permainan untuk mulai belajar dengan cara yang menyenangkan.</p>

          <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Spelling Bee -->
            <a href="{{ route('spelling.index') }}"
               class="group rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 border shadow hover:shadow-lg transition p-6">
              <div class="flex items-start gap-4">
                <div class="text-4xl">🎤</div>
                <div>
                  <h2 class="text-xl font-bold text-amber-700 group-hover:text-amber-800">Spelling Bee</h2>
                  <p class="text-amber-900/70 text-sm">Dengar, eja, jawab! Dukungan TTS & voice input.</p>
                </div>
              </div>
              <div class="mt-4">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs border">
                  Level Beginner & Expert
                </span>
              </div>
            </a>

            <!-- Crossword -->
            <a href="{{ route('crossword.index') }}"
               class="group rounded-2xl bg-gradient-to-br from-blue-50 to-sky-50 border shadow hover:shadow-lg transition p-6">
              <div class="flex items-start gap-4">
                <div class="text-4xl">🧩</div>
                <div>
                  <h2 class="text-xl font-bold text-sky-700 group-hover:text-sky-800">Crossword</h2>
                  <p class="text-sky-900/70 text-sm">Susun kata bertema, grid otomatis, skor per huruf.</p>
                </div>
              </div>
              <div class="mt-4">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-sky-100 text-sky-700 text-xs border">
                  Grid 12×12 / 15×15
                </span>
              </div>
            </a>
          </div>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('leaderboard.spelling') }}" class="px-4 py-2 rounded-full bg-fuchsia-100 text-fuchsia-700 border hover:bg-fuchsia-200">🏆 Leaderboard Spelling</a>
            <a href="{{ route('leaderboard.crossword') }}" class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 border hover:bg-emerald-200">🏆 Leaderboard Crossword</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
