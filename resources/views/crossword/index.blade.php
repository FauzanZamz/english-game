<x-app-layout>
<div x-data="crossword()" class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-blue-50 via-teal-50 to-emerald-50">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex flex-wrap items-end gap-4 mb-6">
      <div>
        <label class="block text-xs font-semibold text-sky-900/80">Tema</label>
        <select x-model="theme" class="mt-1 border rounded-xl px-3 py-2 bg-white/90 focus:ring-teal-300 focus:border-teal-400">
          @foreach($themes as $t)<option value="{{ $t->slug }}">{{ $t->name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-sky-900/80">Level</label>
        <select x-model="level" class="mt-1 border rounded-xl px-3 py-2 bg-white/90 focus:ring-teal-300 focus:border-teal-400">
          <option value="beginner">Beginner</option>
          <option value="expert">Expert</option>
        </select>
      </div>
      <button @click="generate()" class="ml-auto rounded-xl px-5 py-2.5 bg-emerald-500 text-white shadow hover:bg-emerald-600 disabled:opacity-50" :disabled="loading">
        <span x-show="!loading">🧩 Generate</span>
        <span x-show="loading">Generating…</span>
      </button>
      <div class="text-sm font-semibold text-sky-900/80">Skor: <span class="text-emerald-700" x-text="score"></span></div>
    </div>

    <template x-if="error">
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 p-3" x-text="error"></div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
        <template x-if="grid.length">
          <div>
            <div id="grid" class="inline-grid" :style="`grid-template-columns: repeat(${size}, 2.4rem); gap: 3px;`">
              <template x-for="(row, r) in grid" :key="'r'+r">
                <template x-for="(cell, c) in row" :key="'c'+c">
                  <input
                    :data-r="r" :data-c="c" maxlength="1"
                    class="w-10 h-10 text-center rounded-lg border shadow-sm focus:ring-emerald-300 focus:border-emerald-400"
                    :class="{'bg-slate-900/90 text-white border-slate-900': !solution[r][c], 'bg-white/95': solution[r][c]}"
                    :disabled="!solution[r][c]"
                    @input="onCell(r,c,$event)">
                </template>
              </template>
            </div>
            <div class="mt-4">
              <button @click="submit()" class="rounded-xl px-5 py-2.5 bg-sky-500 text-white hover:bg-sky-600 disabled:opacity-50" :disabled="!grid.length">✅ Submit</button>
            </div>
          </div>
        </template>

        <template x-if="!grid.length">
          <p class="text-sky-900/70">Pilih tema & level, lalu klik <b>Generate</b> untuk membuat puzzle.</p>
        </template>
      </div>

      <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
        <h3 class="font-bold text-sky-900 mb-2">Clues</h3>
        <ul class="text-sky-900/80 list-disc ml-5 space-y-1">
          <template x-for="(def, word) in definitions">
            <li><span class="font-semibold text-sky-700" x-text="word"></span>: <span x-text="def"></span></li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
function crossword(){
  return {
    theme: '{{ $themes[0]->slug ?? "animals" }}',
    level: 'beginner',
    grid: [], solution: [], size: 0, definitions: {}, score: 0, t0: null, loading: false, error: '',

    generate(){
      this.error=''; this.loading=true; this.grid=[]; this.solution=[];
      this.t0 = Date.now();
      fetch('{{ route('crossword.generate') }}', {
        method:'POST',
        headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json' },
        body: JSON.stringify({theme:this.theme, level:this.level})
      })
      .then(r => r.json())
      .then(j => {
        if (j.error){ this.error = j.error; return; }
        this.size=j.size; this.solution=j.grid; this.definitions=j.definitions || {};
        // grid input: '' untuk sel huruf; null untuk blok
        this.grid = j.grid.map(row => row.map(cell => cell ? '' : null));
      })
      .catch(() => { this.error = 'Terjadi kesalahan jaringan.'; })
      .finally(() => { this.loading=false; });
    },

    onCell(r,c,e){
      const val = (e.target.value || '').toUpperCase().replace(/[^A-Z]/g,'');
      e.target.value = val;
      if (val && this.grid[r][c] !== null) this.grid[r][c] = val;
    },

    submit(){
      const dur = Math.round((Date.now()-this.t0)/1000);
      fetch('{{ route('crossword.submit') }}', {
        method:'POST',
        headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json' },
        body: JSON.stringify({grid:this.grid, duration_sec: dur})
      })
      .then(r => r.json())
      .then(j => { this.score = j.score; alert(`Benar ${j.correct}/${j.total}. Skor: ${j.score}`); })
      .catch(() => { this.error = 'Gagal submit jawaban.'; });
    }
  }
}
</script>
</x-app-layout>
