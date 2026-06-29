{{-- props: $text (string), $themeName (string|null) --}}
<div class="group rounded-2xl bg-white border shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 p-5 flex flex-col">
  <div class="flex items-start justify-between gap-2">
    <h3 class="text-lg font-bold text-slate-800 capitalize break-words">{{ $text }}</h3>
    @if($themeName)
      <span class="shrink-0 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
        {{ $themeName }}
      </span>
    @endif
  </div>

  <a href="{{ route('kamus.show', ['word' => strtolower($text)]) }}"
     class="mt-4 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl
            bg-gradient-to-r from-emerald-500 to-sky-500 text-white text-sm font-semibold
            shadow hover:from-emerald-600 hover:to-sky-600 transition">
    Lihat Detail
    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
  </a>
</div>
