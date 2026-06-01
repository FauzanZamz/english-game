<x-app-layout>
<div x-data="spellingGame()" class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex flex-wrap items-end gap-4 mb-6">
      <div>
        <label class="block text-xs font-semibold text-sky-900/80">Tema</label>
        <select x-model="theme" class="mt-1 border rounded-xl px-3 py-2 bg-white/90 focus:ring-sky-300 focus:border-sky-400">
          @foreach($themes as $t)<option value="{{ $t->slug }}">{{ $t->name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-sky-900/80">Level</label>
        <select x-model="level" @change="onLevelChange()"
                class="mt-1 border rounded-xl px-3 py-2 bg-white/90 focus:ring-sky-300 focus:border-sky-400">
          <option value="beginner">🌱 Beginner</option>
          <option value="intermediate"
                  :disabled="!spUnlockedLevels.intermediate"
                  x-text="spUnlockedLevels.intermediate ? '🌿 Intermediate' : '🔒 Intermediate (terkunci)'">
          </option>
          <option value="expert"
                  :disabled="!spUnlockedLevels.expert"
                  x-text="spUnlockedLevels.expert ? '🔥 Expert' : '🔒 Expert (terkunci)'">
          </option>
        </select>
      </div>
      <button @click="start()" class="ml-auto rounded-xl px-5 py-2.5 bg-sky-500 text-white shadow hover:bg-sky-600">
        🎮 Mulai (5 kata)
      </button>
      <div class="text-sm font-semibold text-sky-900/80">Skor: <span class="text-sky-700" x-text="score"></span></div>
      <div class="text-sm text-sky-900/80">Progress: <span x-text="roundIdx+1"></span>/5</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: clues & actions -->
      <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
          <div class="flex flex-wrap gap-2 mb-3">
            <button @click="speakWord()" class="rounded-xl px-3 py-2 border bg-yellow-50 text-amber-700 hover:bg-yellow-100">🔊 Dengarkan</button>
            <button @click="rec()" class="rounded-xl px-3 py-2 border bg-fuchsia-50 text-fuchsia-700 hover:bg-fuchsia-100">🎤 Voice</button>
          </div>

          <template x-if="roundActive">
            <div>
              <h3 class="font-bold text-sky-900 mb-2">Clues</h3>
              <ul class="list-disc ml-5 text-sky-900/80 space-y-1">
                <template x-for="c in clues"><li x-text="c"></li></template>
              </ul>

              <div class="mt-4 flex gap-2">
                <input x-model="answer" placeholder="Ketik ejaan..."
                       class="flex-1 rounded-xl border px-3 py-2 focus:ring-sky-300 focus:border-sky-400 bg-white/90"
                       @keyup.enter="submit()">
                <button @click="submit()" class="rounded-xl px-4 py-2 bg-emerald-500 text-white hover:bg-emerald-600">✅ Jawab</button>
                <button @click="giveup()" class="rounded-xl px-4 py-2 bg-rose-500 text-white hover:bg-rose-600">🏳️ Menyerah</button>
              </div>
              <div class="mt-2 text-sm font-medium" :class="{'text-emerald-700': feedback.startsWith('✅'), 'text-rose-700': feedback.startsWith('❌')}" x-text="feedback"></div>
            </div>
          </template>

          <template x-if="!roundActive && answered">
            <div class="text-center">
              <div class="mb-4 text-lg font-semibold" :class="{'text-emerald-700': feedback.startsWith('✅'), 'text-rose-700': feedback.startsWith('❌')}" x-text="feedback"></div>
              <button @click="nextRound()" class="rounded-xl px-6 py-3 bg-sky-500 text-white font-semibold hover:bg-sky-600 shadow-lg">
                ➡️ Kata Berikutnya
              </button>
            </div>
          </template>

          <template x-if="!roundActive && roundIdx===0">
            <p class="text-sky-900/70">Klik <b>Mulai</b> untuk memulai ronde pertama.</p>
          </template>
        </div>
      </div>

      <!-- Right: wiki (tampil hanya setelah jawab) -->
      <div class="space-y-4">
        <template x-if="answered">
          <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
            <h3 class="font-bold text-sky-900">Explanation</h3>
            <p class="text-sky-900/80 mt-1" x-text="wiki.extract"></p>
            <img :src="wiki.image" x-show="wiki.image" class="mt-3 rounded-xl shadow">
          </div>
        </template>

        <template x-if="sessionDone">
          <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
            <p class="font-semibold text-emerald-700">Sesi selesai! Skor: <span x-text="score"></span>/50</p>
            <a href="{{ route('leaderboard.spelling') }}" class="inline-block mt-2 px-4 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700">Lihat Leaderboard</a>
          </div>
        </template>

        {{-- RL: Level unlock banner (shows right after session done) --}}
        <template x-if="spNewLevelUnlocked">
          <div class="rounded-2xl border-2 p-5 mt-2"
               :class="spNewLevelUnlocked === 'expert'
                 ? 'bg-gradient-to-br from-orange-50 to-red-50 border-red-300'
                 : 'bg-gradient-to-br from-green-50 to-cyan-50 border-emerald-300'">
            <div class="flex items-start gap-3">
              <span class="text-3xl" x-text="spNewLevelUnlocked === 'intermediate' ? '🌿' : '🔥'"></span>
              <div class="flex-1">
                <p class="font-bold text-sm uppercase tracking-wide mb-1"
                   :class="spNewLevelUnlocked === 'expert' ? 'text-red-700' : 'text-emerald-700'">
                  Level Baru Terbuka!
                </p>
                <p class="text-sky-900/80 text-sm"
                   x-text="spNewLevelUnlocked === 'intermediate'
                     ? 'Performa Beginner kamu konsisten. Level Intermediate kini tersedia!'
                     : 'Hebat! Kamu menguasai Intermediate. Level Expert kini tersedia!'">
                </p>
                <div class="flex gap-2 mt-3">
                  <button @click="level = spNewLevelUnlocked; spNewLevelUnlocked = null; start();"
                          class="rounded-xl px-4 py-2 font-semibold text-sm text-white shadow"
                          :class="spNewLevelUnlocked === 'expert' ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600'">
                    🚀 Main Sekarang!
                  </button>
                  <button @click="spNewLevelUnlocked = null"
                          class="rounded-xl px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Nanti
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

<script>
function spellingGame(){
  return {
    theme: '{{ $themes[0]->slug ?? "animals" }}',
    level: 'beginner',
    roundIdx: 0, score: 0, clues: [], wiki: {}, wordAudio: '',
    answer: '', feedback: '', roundActive: false, answered: false, sessionDone: false, t0: null,

    /* ══ RL Adaptive Difficulty (3-level) ══
       perf_score = max(0, min(100, round(score / 50 * 100)))
       Unlock thresholds (avg over last 3 same-level sessions):
         beginner  ≥ 60 → unlocks intermediate
         intermediate ≥ 70 → unlocks expert */
    spPerfHistory:    JSON.parse(localStorage.getItem('sp_perf') || '[]'),
    spUnlockedLevels: @json($unlockedLevels),   /* server-side, per-akun */
    spNewLevelUnlocked: null,   /* 'intermediate' | 'expert' | null */

    /* guard: prevent selecting locked levels */
    onLevelChange() {
      if (this.level === 'intermediate' && !this.spUnlockedLevels.intermediate) this.level = 'beginner';
      if (this.level === 'expert'       && !this.spUnlockedLevels.expert)       this.level = 'beginner';
    },

    start(){
      this.roundIdx=0;
      this.score=0;
      this.sessionDone=false;
      this.answered=false;
      this.spNewLevelUnlocked=null;
      this.t0=Date.now();
      this.loadRound();
    },

    loadRound(){
      if (this.roundIdx>=5){ this.finishSession(); return; }
      this.answer=''; 
      this.feedback=''; 
      this.roundActive=true;
      this.answered=false;
      fetch('{{ route('spelling.new') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({theme:this.theme, level:this.level})})
      .then(r=>r.json()).then(j=>{
        if(j.error){ this.feedback=j.error; this.roundActive=false; return; }
        this.clues=j.clues; this.wiki=j.wiki; this.wordAudio=j.wordAudio;
        this.speakWord();
      })
    },

    submit(){
      fetch('{{ route('spelling.answer') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({answer:this.answer})})
      .then(r=>r.json()).then(j=>{
        this.score+=j.scoreDelta; 
        this.feedback = j.correct?'✅ Benar!':'❌ Salah. Jawaban: '+j.expected;
        this.roundActive=false; 
        this.answered=true;
      })
    },

    giveup(){
      fetch('{{ route('spelling.answer') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({answer:'', giveup:true})})
      .then(r=>r.json()).then(j=>{
        this.feedback = '🏳️ Menyerah. Kata: '+j.expected; 
        this.roundActive=false; 
        this.answered=true;
      })
    },

    nextRound(){
      this.roundIdx++;
      this.loadRound();
    },

    speakWord(){
      if(!this.wordAudio) return;
      const u = new SpeechSynthesisUtterance(this.wordAudio);
      u.lang='en-US'; speechSynthesis.speak(u);
    },

    rec(){
      try{
        const R = window.SpeechRecognition || window.webkitSpeechRecognition;
        if(!R){ alert('Browser tidak mendukung voice recognition'); return; }
        const rec = new R(); rec.lang='en-US'; rec.onresult=(e)=>{ this.answer = e.results[0][0].transcript.replace(/\s+/g,'').toLowerCase(); };
        rec.start();
      }catch(e){ console.log(e); }
    },

    finishSession(){
      this.sessionDone = true;
      const dur = Math.round((Date.now() - this.t0) / 1000);

      /* RL perf_score: normalize session score (max 50) to 0–100 */
      const perfScore = Math.max(0, Math.min(100, Math.round(Math.max(0, this.score) / 50 * 100)));

      /* Append to history, cap at 20 entries */
      this.spPerfHistory.push({ score: perfScore, level: this.level, ts: Date.now() });
      if (this.spPerfHistory.length > 20) this.spPerfHistory.shift();
      try { localStorage.setItem('sp_perf', JSON.stringify(this.spPerfHistory)); } catch(e) {}

      /* RL unlock: avg of last 3 sessions AT THIS LEVEL */
      const sameLvl  = this.spPerfHistory.filter(h => h.level === this.level).slice(-3);
      const levelAvg = sameLvl.length > 0
        ? Math.round(sameLvl.reduce((s, x) => s + x.score, 0) / sameLvl.length)
        : 0;

      let justUnlocked = null;
      if (this.level === 'beginner' && levelAvg >= 60 && !this.spUnlockedLevels.intermediate) {
        this.spUnlockedLevels = { ...this.spUnlockedLevels, intermediate: true };
        justUnlocked = 'intermediate';
      } else if (this.level === 'intermediate' && levelAvg >= 70 && !this.spUnlockedLevels.expert) {
        this.spUnlockedLevels = { ...this.spUnlockedLevels, expert: true };
        justUnlocked = 'expert';
      }
      if (justUnlocked) {
        this.spNewLevelUnlocked = justUnlocked;
        /* Simpan unlock ke server (per-akun) */
        fetch('{{ route('spelling.unlock') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
          body: JSON.stringify({ level: justUnlocked })
        });
      }

      fetch('{{ route('spelling.finish') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ duration_sec: dur })
      });
    }
  }
}
</script>
</x-app-layout>