<x-app-layout>
  <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-emerald-50 via-sky-50 to-blue-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

      {{-- ── Header ── --}}
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center shadow">
          <span class="text-2xl">📖</span>
        </div>
        <div>
          <h1 class="text-2xl font-extrabold text-slate-800">Dictionary</h1>
          <p class="text-sm text-slate-500">Search words, view definitions & Wikipedia summaries.</p>
        </div>
      </div>

      {{-- ── Search bar ── --}}
      <form method="GET" action="{{ route('kamus.search') }}"
            class="rounded-2xl bg-white/90 backdrop-blur border shadow p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
            <input type="text" name="q" value="{{ $query }}"
                   placeholder="Type a word to search…"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-slate-700
                          focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 transition">
          </div>
          <button type="submit"
                  class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-sky-500 text-white font-semibold
                         shadow hover:shadow-md hover:from-emerald-600 hover:to-sky-600 transition">
            Search
          </button>
        </div>
      </form>

      {{-- ── Filter tema (pill) ── --}}
      <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('kamus.index') }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                  {{ is_null($activeSlug) && is_null($words)
                       ? 'bg-emerald-500 text-white border-emerald-500 shadow'
                       : 'bg-white text-slate-600 border-slate-200 hover:bg-emerald-50 hover:text-emerald-700' }}">
          All Themes
        </a>
        @foreach($themes as $theme)
          <a href="{{ route('kamus.search', ['theme' => $theme->slug] + ($query !== '' ? ['q' => $query] : [])) }}"
             class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ $activeSlug === $theme->slug
                         ? 'bg-emerald-500 text-white border-emerald-500 shadow'
                         : 'bg-white text-slate-600 border-slate-200 hover:bg-emerald-50 hover:text-emerald-700' }}">
            {{ $theme->name }}
          </a>
        @endforeach
      </div>

      @if(!is_null($words))
        {{-- ════════ MODE: hasil pencarian ════════ --}}
        <div class="mb-4 text-sm text-slate-500">
          @if($query !== '')
            Results for <span class="font-semibold text-slate-700">"{{ $query }}"</span> —
          @endif
          {{ $words->total() }} word(s) found.
        </div>

        @if($words->count() > 0)
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($words as $word)
              @include('kamus.partials.word-card', [
                'text'      => $word->text,
                'themeName' => $word->theme?->name,
              ])
            @endforeach
          </div>

          <div class="mt-6">
            {{ $words->links() }}
          </div>
        @else
          @include('kamus.partials.empty', ['query' => $query])
        @endif

      @else
        {{-- ════════ MODE: browse per tema ════════ --}}
        @php $hasAny = $themes->contains(fn ($t) => $t->words->count() > 0); @endphp

        @if($hasAny)
          @foreach($themes as $theme)
            @if($theme->words->count() > 0)
              <section class="mb-8">
                <div class="flex items-center gap-2 mb-3">
                  <h2 class="text-lg font-bold text-slate-700">{{ $theme->name }}</h2>
                  <span class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold">
                    {{ $theme->words->count() }} word(s)
                  </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  @foreach($theme->words as $word)
                    @include('kamus.partials.word-card', [
                      'text'      => $word->text,
                      'themeName' => $theme->name,
                    ])
                  @endforeach
                </div>
              </section>
            @endif
          @endforeach
        @else
          @include('kamus.partials.empty', ['query' => ''])
        @endif
      @endif

    </div>
  </div>
</x-app-layout>
