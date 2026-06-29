<x-app-layout>
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-amber-50 via-sky-50 to-indigo-50">
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

  {{-- ── Header ── --}}
  <div class="flex items-center gap-3 mb-6">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow">
      <span class="text-2xl">🏆</span>
    </div>
    <div>
      <h1 class="text-2xl font-extrabold text-slate-800">Leaderboard</h1>
      <p class="text-sm text-slate-500">Top 20 pemain terbaik per game & difficulty.</p>
    </div>
  </div>

  {{-- ── Tab: pilih game ── --}}
  <div class="flex gap-2 mb-5 flex-wrap">
    @foreach([
      'spelling-bee' => ['label' => '🎤 Spelling Bee', 'color' => 'amber'],
      'crossword'    => ['label' => '🧩 Crossword',    'color' => 'sky'],
    ] as $slug => $cfg)
      @php
        $active = $gameSlug === $slug;
        $base   = "px-5 py-2.5 rounded-xl text-sm font-semibold border transition-all duration-200 ";
        $on     = $cfg['color'] === 'amber'
          ? 'bg-amber-500 text-white border-amber-500 shadow-md'
          : 'bg-sky-500 text-white border-sky-500 shadow-md';
        $off    = 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50';
      @endphp
      <a href="{{ route('leaderboard.index', ['game' => $slug, 'level' => $level]) }}"
         class="{{ $base . ($active ? $on : $off) }}">
        {{ $cfg['label'] }}
      </a>
    @endforeach
  </div>

  {{-- ── Tab: pilih difficulty ── --}}
  <div class="flex gap-2 mb-6 flex-wrap">
    @foreach($levelLabels as $lv => $label)
      @php
        $active = $level === $lv;
        $colors = match($lv) {
          'beginner'     => ['on' => 'bg-emerald-500 text-white border-emerald-500 shadow-md',    'off' => 'bg-white text-emerald-700 border-emerald-200 hover:bg-emerald-50'],
          'intermediate' => ['on' => 'bg-blue-500 text-white border-blue-500 shadow-md',          'off' => 'bg-white text-blue-700 border-blue-200 hover:bg-blue-50'],
          default        => ['on' => 'bg-rose-500 text-white border-rose-500 shadow-md',          'off' => 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50'],
        };
      @endphp
      <a href="{{ route('leaderboard.index', ['game' => $gameSlug, 'level' => $lv]) }}"
         class="px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200
                {{ $active ? $colors['on'] : $colors['off'] }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  {{-- ── Active context pill ── --}}
  <div class="mb-4 text-sm text-slate-500">
    Menampilkan top 20 —
    <span class="font-semibold text-slate-700">{{ $game->name }}</span>
    ·
    <span class="font-semibold text-slate-700">{{ $levelLabels[$level] }}</span>
  </div>

  {{-- ── Tabel ── --}}
  <div class="rounded-2xl bg-white/90 backdrop-blur border shadow overflow-hidden">
    @if($top->count() > 0)
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-gradient-to-r
            {{ $gameSlug === 'spelling-bee' ? 'from-amber-50 to-yellow-50' : 'from-sky-50 to-indigo-50' }}
            border-b">
            <th class="px-5 py-3 text-left font-semibold text-slate-600 w-14">Rank</th>
            <th class="px-5 py-3 text-left font-semibold text-slate-600">Pemain</th>
            <th class="px-5 py-3 text-center font-semibold text-slate-600">Skor</th>
            <th class="px-5 py-3 text-center font-semibold text-slate-600 hidden sm:table-cell">Waktu</th>
            <th class="px-5 py-3 text-center font-semibold text-slate-600 hidden md:table-cell">Tanggal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($top as $i => $play)
            @php $rank = $i + 1; @endphp
            <tr class="transition-colors hover:bg-slate-50
                       {{ $rank <= 3 ? 'bg-gradient-to-r from-yellow-50/50 to-transparent' : '' }}">

              {{-- Rank --}}
              <td class="px-5 py-3.5 font-bold text-center">
                @if($rank === 1) <span class="text-xl">🥇</span>
                @elseif($rank === 2) <span class="text-xl">🥈</span>
                @elseif($rank === 3) <span class="text-xl">🥉</span>
                @else <span class="text-slate-400">#{{ $rank }}</span>
                @endif
              </td>

              {{-- Player --}}
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm
                    {{ $gameSlug === 'spelling-bee' ? 'bg-gradient-to-br from-amber-400 to-orange-500' : 'bg-gradient-to-br from-sky-400 to-indigo-500' }}">
                    {{ strtoupper(substr($play->user->name, 0, 1)) }}
                  </div>
                  <div>
                    <div class="font-semibold text-slate-800">{{ $play->user->name }}</div>
                    @if($rank <= 3)
                      <div class="text-xs text-amber-600 font-medium">Top {{ $rank }}</div>
                    @endif
                  </div>
                </div>
              </td>

              {{-- Score --}}
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold
                  {{ $rank === 1 ? 'bg-yellow-100 text-yellow-700' : ($rank <= 3 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-600') }}">
                  {{ $play->score }}
                </span>
              </td>

              {{-- Duration --}}
              <td class="px-5 py-3.5 text-center text-slate-500 hidden sm:table-cell">
                @php
                  $m = intdiv($play->duration_sec, 60);
                  $s = $play->duration_sec % 60;
                @endphp
                {{ $m > 0 ? $m.'m ' : '' }}{{ $s }}s
              </td>

              {{-- Date --}}
              <td class="px-5 py-3.5 text-center text-slate-400 text-xs hidden md:table-cell">
                {{ $play->created_at->format('d M Y') }}<br>
                <span class="text-slate-300">{{ $play->created_at->format('H:i') }}</span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="py-16 text-center">
        <div class="text-5xl mb-3">🏅</div>
        <h3 class="text-lg font-bold text-slate-600">Belum ada data</h3>
        <p class="mt-1 text-sm text-slate-400">
          Belum ada pemain yang menyelesaikan
          <span class="font-semibold">{{ $game->name }}</span>
          di level <span class="font-semibold">{{ $levelLabels[$level] }}</span>.
        </p>
        @if($gameSlug === 'spelling-bee')
          <a href="{{ route('spelling.index') }}"
             class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition">
            Mainkan Spelling Bee
          </a>
        @else
          <a href="{{ route('crossword.index') }}"
             class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-sky-500 text-white text-sm font-semibold hover:bg-sky-600 transition">
            Mainkan Crossword
          </a>
        @endif
      </div>
    @endif
  </div>

</div>
</div>
</x-app-layout>
