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
                  <div class="relative w-10 h-10">
                    <input
                      :data-r="r" :data-c="c" maxlength="1"
                      class="absolute inset-0 w-full h-full text-center rounded-lg border shadow-sm focus:ring-emerald-300 focus:border-emerald-400"
                      :class="{ 'bg-slate-900/90 text-white border-slate-900': !solution[r][c], 'bg-white/95': solution[r][c], 'ring-2 ring-emerald-400 bg-amber-50': isActiveCell(r,c) }"
                      :disabled="!solution[r][c]"
                      @input="onCell(r,c,$event)"
                      @click="onCellClick(r,c,$event)"
                      @keydown.backspace="onBackspace(r,c,$event)"
                      @keydown.delete="onDelete(r,c,$event)">
                    <div v-show="getCellNumber(r,c)" class="absolute top-0 left-0 text-xs font-bold text-sky-700 pointer-events-none leading-none" style="font-size: 7px; padding: 1px 2px;" x-text="getCellNumber(r,c)"></div>
                  </div>
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
        <h3 class="font-bold text-sky-900 mb-3">Clues</h3>

        <template x-if="!Object.keys(activeClues || {}).length">
          <p class="text-sky-900/70 italic text-sm">Klik kotak untuk melihat definisi</p>
        </template>

        <template x-if="activeClues && activeClues.across">
          <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
            <p class="text-sm font-semibold text-emerald-700 mb-1">📋 Mendatar</p>
            <p class="text-sky-900/80" x-text="definitions[activeClues.across]"></p>
          </div>
        </template>

        <template x-if="activeClues && activeClues.down">
          <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-semibold text-blue-700 mb-1">📋 Menurun</p>
            <p class="text-sky-900/80" x-text="definitions[activeClues.down]"></p>
          </div>
        </template>
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
    positions: {}, // word -> {row,col,direction,length,number}
    coordMap: {}, // "r,c" -> {across:word, down:word}
    cellNumbers: {}, // "r,c" -> nomor klue
    currentCell: null,
    activeClues: {},

    generate(){
      this.error=''; this.loading=true; this.grid=[]; this.solution=[]; this.positions={}; this.coordMap={}; this.cellNumbers={}; this.activeClues={};
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
        this.positions = j.positions || {};
        // grid input: '' untuk sel huruf; null untuk blok
        this.grid = j.grid.map(row => row.map(cell => cell ? '' : null));
        this.buildCoordMap();
        this.buildCellNumbers();
      })
      .catch(() => { this.error = 'Terjadi kesalahan jaringan.'; })
      .finally(() => { this.loading=false; });
    },

    buildCoordMap(){
      this.coordMap = {};
      for (let w in this.positions){
        const p = this.positions[w];
        if (p.direction === 'across'){
          for (let i=0;i<p.length;i++){
            const key = `${p.row},${p.col+i}`;
            if (!this.coordMap[key]) this.coordMap[key]={};
            this.coordMap[key].across = w;
          }
        } else {
          for (let i=0;i<p.length;i++){
            const key = `${p.row+i},${p.col}`;
            if (!this.coordMap[key]) this.coordMap[key]={};
            this.coordMap[key].down = w;
          }
        }
      }
    },

    buildCellNumbers(){
      this.cellNumbers = {};
      for (let w in this.positions){
        const p = this.positions[w];
        const key = `${p.row},${p.col}`;
        // simpan nomor di awal kata
        if (!this.cellNumbers[key]) this.cellNumbers[key] = p.number;
        else this.cellNumbers[key] = Math.min(this.cellNumbers[key], p.number);
      }
    },

    getCellNumber(r,c){
      return this.cellNumbers[`${r},${c}`] || null;
    },

    onCell(r,c,e){
      const val = (e.target.value || '').toUpperCase().replace(/[^A-Z]/g,'');
      e.target.value = val;
      if (val && this.grid[r][c] !== null) this.grid[r][c] = val;

      const coord = this.coordMap[`${r},${c}`] || {};
      const dir = (this.currentCell && this.currentCell.direction) || (coord.across ? 'across' : (coord.down ? 'down' : null));
      this.currentCell = { r, c, direction: dir, word: coord[dir] || null };

      if (val && this.currentCell && this.currentCell.word){
        this.focusNextInWord(r,c,this.currentCell.direction);
      }
    },

    onCellClick(r,c,e){
      const coord = this.coordMap[`${r},${c}`] || {};
      // jika klik kotak yang sama dan ada kedua arah, toggle arah
      if (this.currentCell && this.currentCell.r === r && this.currentCell.c === c && coord.across && coord.down){
        // toggle
        const newDir = this.currentCell.direction === 'across' ? 'down' : 'across';
        this.currentCell.direction = newDir;
        this.currentCell.word = coord[newDir];
      } else {
        const dir = coord.across ? 'across' : (coord.down ? 'down' : null);
        this.currentCell = { r, c, direction: dir, word: coord[dir] || null };
      }
      this.activeClues = { across: coord.across || null, down: coord.down || null };
    },

    // return true jika (r,c) termasuk dalam kata aktif (currentCell.word & direction)
    isActiveCell(r,c){
      if (!this.currentCell || !this.currentCell.word || !this.currentCell.direction) return false;
      const word = this.currentCell.word;
      const pos = this.positions[word];
      if (!pos) return false;
      if (pos.direction === 'across'){
        return r === pos.row && c >= pos.col && c < pos.col + pos.length;
      } else {
        return c === pos.col && r >= pos.row && r < pos.row + pos.length;
      }
    },

    focusNextInWord(r,c,direction){
      const coord = this.coordMap[`${r},${c}`] || {};
      const dir = direction || (coord.across ? 'across' : (coord.down ? 'down' : null));
      if (!dir) return;
      const word = coord[dir];
      const pos = this.positions[word];
      if (!pos) return;
      const idx = (dir === 'across') ? (c - pos.col) : (r - pos.row);
      const nextIdx = idx + 1;
      if (nextIdx >= pos.length) return;
      const nextR = pos.row + (dir === 'down' ? nextIdx : 0);
      const nextC = pos.col + (dir === 'across' ? nextIdx : 0);
      if (this.solution[nextR] && this.solution[nextR][nextC]){
        setTimeout(()=>{
          const input = document.querySelector(`input[data-r="${nextR}"][data-c="${nextC}"]`);
          if (input) input.focus();
        },50);
      }
    },

    focusPrevInWord(r,c){
      const coord = this.coordMap[`${r},${c}`] || {};
      const dir = (this.currentCell && this.currentCell.direction) || (coord.across ? 'across' : (coord.down ? 'down' : null));
      if (!dir) return;
      const word = coord[dir];
      const pos = this.positions[word];
      if (!pos) return;
      const idx = (dir === 'across') ? (c - pos.col) : (r - pos.row);
      const prevIdx = idx - 1;
      if (prevIdx < 0) return;
      const prevR = pos.row + (dir === 'down' ? prevIdx : 0);
      const prevC = pos.col + (dir === 'across' ? prevIdx : 0);
      if (this.solution[prevR] && this.solution[prevR][prevC]){
        setTimeout(()=>{
          const input = document.querySelector(`input[data-r="${prevR}"][data-c="${prevC}"]`);
          if (input) input.focus();
        },50);
      }
    },

    onBackspace(r,c,e){
      const input = e.target;
      if (!input.value){
        e.preventDefault();
        this.focusPrevInWord(r,c);
      } else {
        e.preventDefault();
        input.value = '';
        if (this.grid[r][c] !== null) this.grid[r][c] = '';
        this.currentCell = { r, c, ...this.coordMap[`${r},${c}`] };
      }
    },

    onDelete(r,c,e){
      e.preventDefault();
      const input = e.target;
      input.value = '';
      if (this.grid[r][c] !== null) this.grid[r][c] = '';
      this.currentCell = { r, c, ...this.coordMap[`${r},${c}`] };
      this.focusPrevInWord(r,c);
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
