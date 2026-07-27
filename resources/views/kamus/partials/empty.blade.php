{{-- props: $query (string) --}}
<div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-10 text-center">
  <div class="text-5xl mb-3">🔍</div>
  <h3 class="text-lg font-bold text-slate-700">No words found</h3>
  <p class="mt-1 text-sm text-slate-500">
    @if(!empty($query))
      No words matched <span class="font-semibold">"{{ $query }}"</span>.
      Try a different keyword.
    @else
      No words are available in the dictionary yet.
    @endif
  </p>
  <a href="{{ route('kamus.index') }}"
     class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700
            text-sm font-semibold hover:bg-emerald-200 transition">
    ← Back to all themes
  </a>
</div>
