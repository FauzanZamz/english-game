<x-app-layout>
  <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-emerald-50 via-sky-50 to-blue-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

      <a href="{{ route('kamus.index') }}"
         class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-emerald-700 transition mb-5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Dictionary
      </a>

      <div class="rounded-3xl bg-white/90 backdrop-blur border shadow-lg overflow-hidden">

        <div class="bg-gradient-to-r from-emerald-500 to-sky-500 px-6 sm:px-8 py-6">
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white capitalize break-words">{{ $word }}</h1>
            @if($theme)
              <span class="px-3 py-1 rounded-full bg-white/25 text-white text-sm font-semibold backdrop-blur">
                {{ $theme->name }}
              </span>
            @endif
          </div>
        </div>

        <div class="px-6 sm:px-8 py-6 space-y-6">

          @if(!empty($wiki['image']))
            <div class="rounded-2xl overflow-hidden border bg-slate-50">
              <img src="{{ $wiki['image'] }}" alt="{{ $word }}"
                   class="w-full max-h-80 object-cover">
            </div>
          @endif

          <section>
            <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-emerald-700 mb-3">
              <span>📚</span> Definitions
            </h2>
            @if(count($defs) > 0)
              <ul class="space-y-2.5">
                @foreach($defs as $def)
                  <li class="flex gap-2 text-slate-700 leading-relaxed">
                    <span class="shrink-0 mt-2 w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>{{ ltrim($def, '- ') }}</span>
                  </li>
                @endforeach
              </ul>
            @else
              <p class="text-sm text-slate-400 italic">No definition available for this word.</p>
            @endif
          </section>

          @if(!empty($wiki['extract']))
            <section>
              <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-sky-700 mb-3">
                <span>🌐</span> About (Wikipedia)
              </h2>
              <p class="text-slate-700 leading-relaxed">{{ $wiki['extract'] }}</p>
              <p class="mt-2 text-xs text-slate-400">
                Source:
                <a href="https://en.wikipedia.org/wiki/{{ urlencode(ucfirst($word)) }}"
                   target="_blank" rel="noopener"
                   class="text-sky-600 underline hover:text-sky-700">Wikipedia</a>
              </p>
            </section>
          @endif

          @if(count($defs) === 0 && empty($wiki['extract']) && empty($wiki['image']))
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-center">
              <div class="text-3xl mb-2">🤔</div>
              <p class="text-sm text-amber-800">
                No information found for this word.
              </p>
            </div>
          @endif

        </div>
      </div>

    </div>
  </div>
</x-app-layout>
