<x-app-layout>
<div x-data="spellingGame()" class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50">
  <div class="max-w-7xl mx-auto p-6">

    {{-- ── Page title row (mirroring Crossword structure) ── --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 mt-2">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-pink-500 flex items-center justify-center shadow-sm">
          <span style="font-size:1.15rem">🐝</span>
        </div>
        <div>
          <h1 style="margin:0;font-size:1.2rem;font-weight:800;color:#1e293b;line-height:1.2">Spelling Bee</h1>
          <p style="margin:0;font-size:0.7rem;color:#94a3b8;font-weight:500">Listen, spell, and level up!</p>
        </div>
      </div>

      {{-- Timer + Score pills — tampil setelah sesi dimulai --}}
      <div x-show="started" style="margin-left:auto;display:flex;align-items:center;gap:10px">
        {{-- Timer pill --}}
        <div style="display:flex;align-items:center;gap:7px;background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:10px;padding:6px 14px">
          <svg style="width:15px;height:15px;color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span style="font-family:monospace;font-size:0.85rem;font-weight:700;color:#1e293b" x-text="timerDisplay">00:00</span>
        </div>
        {{-- Score pill --}}
        <div style="display:flex;align-items:center;gap:7px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:6px 14px">
          <span style="font-size:0.72rem;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.05em">Score</span>
          <span style="font-size:1rem;font-weight:800;color:#166534" x-text="score">0</span>
          <span style="font-size:0.72rem;color:#86efac">/ <span x-text="targetWords * 10"></span></span>
        </div>
      </div>
    </div>

    {{-- ══ Control bar: tema, level, mulai ══ --}}
    <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-4 mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs font-semibold text-sky-900/80 mb-1">THEME</label>
          <select x-model="theme" :disabled="inProgress"
                  class="border rounded-xl px-3 py-2 bg-white/90 focus:ring-sky-300 focus:border-sky-400 disabled:opacity-50 disabled:cursor-not-allowed">
            @foreach($themes as $t)<option value="{{ $t->slug }}">{{ $t->name }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-sky-900/80 mb-1">DIFFICULTY</label>
          <select x-model="level" @change="onLevelChange()" :disabled="inProgress"
                  class="border rounded-xl px-3 py-2 bg-white/90 focus:ring-sky-300 focus:border-sky-400 disabled:opacity-50 disabled:cursor-not-allowed">
            <option value="beginner">🌱 Beginner — 5 words</option>
            <option value="intermediate"
                    :disabled="!spUnlockedLevels.intermediate"
                    x-text="spUnlockedLevels.intermediate ? '🌿 Intermediate — 10 words' : '🔒 Intermediate (locked)'">
            </option>
            <option value="expert"
                    :disabled="!spUnlockedLevels.expert"
                    x-text="spUnlockedLevels.expert ? '🔥 Expert & Beyond — 20 words' : '🔒 Expert & Beyond (locked)'">
            </option>
          </select>
        </div>

        <button @click="start()" :disabled="loadingRound"
                class="ml-auto rounded-xl px-5 py-2.5 bg-sky-500 text-white font-semibold shadow hover:bg-sky-600 disabled:opacity-60 disabled:cursor-not-allowed"
                x-text="started ? '🔄 Try Again · ' + targetWords + ' words' : '🎮 Start · ' + wordsForLevel(level) + ' words'">
        </button>
      </div>

      {{-- ══ Progress + skor (tampil setelah mulai) ══ --}}
      <template x-if="started">
        <div class="mt-4 pt-4 border-t flex items-center gap-4 flex-wrap">
          <div class="flex-1 min-w-[200px]">
            <div class="flex justify-between text-xs font-semibold text-sky-900/70 mb-1">
              <span x-text="sessionDone ? 'Finished!' : ('Word ' + Math.min(roundIdx + 1, targetWords) + ' / ' + targetWords)"></span>
              <span x-text="answeredCount() + ' / ' + targetWords + ' answered'"></span>
            </div>
            <div class="h-2.5 rounded-full bg-sky-100 overflow-hidden">
              <div class="h-full bg-gradient-to-r from-sky-400 to-emerald-400 transition-all duration-500"
                   :style="'width:' + (answeredCount() / targetWords * 100) + '%'"></div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- ══ Left: arena permainan ══ --}}
      <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5 min-h-[280px]">

          {{-- State: belum mulai --}}
          <template x-if="!started">
            <div class="text-center py-10">
              <div class="text-5xl mb-3">🐝</div>
              <h3 class="font-bold text-sky-900 text-lg mb-1">Spelling Bee</h3>
              <p class="text-sky-900/60 text-sm max-w-sm mx-auto">
                Listen to the word, read the clue, then type the correct spelling.
                Choose a theme &amp; difficulty, then click <b>Start</b> to begin.
              </p>
            </div>
          </template>

          {{-- State: sedang memuat kata --}}
          <template x-if="loadingRound">
            <div class="text-center py-12">
              <div class="text-4xl mb-3 animate-pulse">🔄</div>
              <p class="text-sky-900/70 font-medium">Preparing word…</p>
            </div>
          </template>

          {{-- State: ronde aktif --}}
          <template x-if="roundActive && !loadingRound">
            <div>
              {{-- Audio controls --}}
              <div class="flex flex-wrap gap-2 mb-4">
                <button @click="speakWord()"
                        class="rounded-xl px-3 py-2 border bg-yellow-50 text-amber-700 hover:bg-yellow-100">
                  🔊 Listen
                </button>
                <button @click="rec()"
                        class="rounded-xl px-4 py-2.5 border border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700 hover:bg-fuchsia-100 flex items-center gap-2">
                  🎤 Voice
                </button>
              </div>

              <h3 class="font-bold text-sky-900 mb-2 flex items-center gap-2">💡 Clues</h3>
              <ul class="list-disc ml-5 text-sky-900/80 space-y-1">
                <template x-for="c in clues"><li x-text="c"></li></template>
              </ul>

              <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <input x-model="answer" placeholder="Ketik ejaan kata…" :disabled="busy"
              class="w-full sm:flex-1 rounded-xl border px-3 py-2 focus:ring-sky-300 focus:border-sky-400 bg-white/90 disabled:opacity-60"
              @keyup.enter="submit()" x-ref="answerInput">

    <div class="flex gap-2">
        <button @click="submit()" :disabled="busy || !answer.trim()"
                class="flex-1 sm:flex-none justify-center items-center flex rounded-xl px-4 py-2 bg-emerald-500 text-white font-semibold hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed">
            ✅ Answer
        </button>
        <button @click="giveup()" :disabled="busy"
                class="flex-1 sm:flex-none justify-center items-center flex rounded-xl px-4 py-2 bg-rose-500 text-white hover:bg-rose-600 disabled:opacity-50">
            🏳️ Surrender
                </button>
              </div>
            </div>
          </template>

          {{-- State: ronde terjawab (belum sesi selesai) --}}
          <template x-if="answered && !sessionDone">
            <div class="text-center py-6">
              <div class="mb-5 text-lg font-semibold"
                   :class="{'text-emerald-700': feedback.startsWith('✅'), 'text-rose-700': feedback.startsWith('❌'), 'text-amber-700': feedback.startsWith('🏳️')}"
                   x-text="feedback"></div>
              <button @click="nextRound()"
                      class="rounded-xl px-6 py-3 bg-sky-500 text-white font-semibold hover:bg-sky-600 shadow-lg"
                      x-text="(roundIdx + 1 >= targetWords) ? '🏁 View Results' : '➡️ Next Word'">
              </button>
            </div>
          </template>

          {{-- State: error saat memuat --}}
          <template x-if="roundError">
            <div class="text-center py-8">
              <p class="text-rose-600 font-medium mb-3" x-text="roundError"></p>
              <button @click="loadRound()" class="rounded-xl px-5 py-2 bg-sky-500 text-white hover:bg-sky-600">
                🔄 Try Again
              </button>
            </div>
          </template>
        </div>
      </div>

      {{-- ══ Right: penjelasan & hasil ══ --}}
      <div class="space-y-4">
        <template x-if="answered && wiki.extract">
          <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
            <h3 class="font-bold text-sky-900">📖 Explanation</h3>
            <p class="text-sky-900/80 mt-1 text-sm leading-relaxed" x-text="wiki.extract"></p>
            <img :src="wiki.image" x-show="wiki.image" class="mt-3 rounded-xl shadow" alt="">
          </div>
        </template>

        {{-- ══ Unified Result Card (Result + GBA + Recommendation) ══ --}}
        <template x-if="sessionDone">
          <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">

            {{-- Title row --}}
            <div class="flex items-center gap-3 mb-4">
              <span class="text-2xl">🎉</span>
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hasil Sesi</p>
                <p class="font-bold text-slate-800">Sesi selesai!</p>
              </div>
            </div>

            {{-- Stats: Skor · Benar · Waktu --}}
            <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 rounded-xl mb-4">
              <div class="text-center">
                <div class="text-xl font-bold text-sky-600" x-text="score"></div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mt-0.5">Skor</div>
              </div>
              <div class="text-center border-x border-slate-200">
                <div class="text-xl font-bold text-emerald-600" x-text="correctCount + '/' + targetWords"></div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mt-0.5">Benar</div>
              </div>
              <div class="text-center">
                <div class="text-xl font-bold text-amber-500" x-text="timerDisplay"></div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mt-0.5">Waktu</div>
              </div>
            </div>

            {{-- GBA Assessment (tampil bila data tersedia) --}}
            <div x-show="showGbaCard" class="border-t pt-3 mb-3">
              <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">📊 Game-Based Assessment</p>
              <div class="grid grid-cols-2 gap-2">
                <div class="text-center bg-indigo-50 rounded-lg p-2.5">
                  <div class="text-lg font-bold text-indigo-700" x-text="(gbaTheta * 100).toFixed(1) + '%'"></div>
                  <div class="text-xs text-indigo-400 mt-0.5">Kemampuan (θ)</div>
                </div>
                <div class="text-center bg-purple-50 rounded-lg p-2.5">
                  <div class="text-lg font-bold text-purple-700" x-text="(gbaLdNext * 100).toFixed(1) + '%'"></div>
                  <div class="text-xs text-purple-400 mt-0.5">Difficulty Berikutnya</div>
                </div>
              </div>
            </div>

            {{-- Adaptive Recommendation (inline) --}}
            <div x-show="spDiffRecommendation" class="flex items-start gap-2 bg-slate-50 rounded-lg p-3 mb-4">
              <span class="text-base mt-0.5">🧠</span>
              <div class="flex-1 min-w-0">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold border mb-1"
                      :class="{
                        'bg-amber-50 text-amber-800 border-amber-200': spDiffRecommendation?.type === 'unlock',
                        'bg-sky-50 text-sky-700 border-sky-200':       spDiffRecommendation?.type === 'progress',
                        'bg-slate-50 text-slate-600 border-slate-200': spDiffRecommendation?.type === 'info'
                      }"
                      x-text="spDiffRecommendation?.label"></span>
                <p class="text-xs text-slate-600 leading-relaxed" x-text="spDiffRecommendation?.message"></p>
              </div>
            </div>

            <a href="{{ route('leaderboard.spelling') }}"
               class="inline-block px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
              View Leaderboard
            </a>
          </div>
        </template>

        {{-- RL: Level unlock banner --}}
        <template x-if="spNewLevelUnlocked">
          <div class="rounded-2xl border-2 p-5"
               :class="spNewLevelUnlocked === 'expert'
                 ? 'bg-gradient-to-br from-orange-50 to-red-50 border-red-300'
                 : 'bg-gradient-to-br from-green-50 to-cyan-50 border-emerald-300'">
            <div class="flex items-start gap-3">
              <span class="text-3xl" x-text="spNewLevelUnlocked === 'intermediate' ? '🌿' : '🔥'"></span>
              <div class="flex-1">
                <p class="font-bold text-sm uppercase tracking-wide mb-1"
                   :class="spNewLevelUnlocked === 'expert' ? 'text-red-700' : 'text-emerald-700'">
                  Next Difficulty Unlocked!
                </p>
                <p class="text-sky-900/80 text-sm"
                   x-text="spNewLevelUnlocked === 'intermediate'
                     ? 'Your Beginner performance is consistent. Intermediate level is now available!'
                     : 'Great job! You have mastered Intermediate. Expert &amp; Beyond level is now available!'">
                </p>
                <div class="flex gap-2 mt-3">
                  <button @click="level = spNewLevelUnlocked; spNewLevelUnlocked = null; start();"
                          class="rounded-xl px-4 py-2 font-semibold text-sm text-white shadow"
                          :class="spNewLevelUnlocked === 'expert' ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600'">
                    🚀 Play Now!
                  </button>
                  <button @click="spNewLevelUnlocked = null"
                          class="rounded-xl px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Later
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
    targetWords: 5,           /* jumlah kata sesi berjalan (dikunci saat start) */
    roundIdx: 0, score: 0, clues: [], wiki: {}, wordAudio: '',
    answer: '', feedback: '', roundError: '',
    started: false, roundActive: false, answered: false, sessionDone: false,
    loadingRound: false, busy: false, t0: null,
    timerDisplay: '00:00', timerInterval: null,

    /* GBA/DDA state */
    gbaTheta:         0,
    gbaLdNext:        0.30,
    gbaLdCurrent:     0.30,
    hintsUsed:        0,
    hintsAvailable:   3,
    showGbaCard:      false,

    /* ══ RL Adaptive Difficulty (3-level) — formula identik Crossword ══
       perf_score (0–100):
         accuracy*60  — kata benar / total ronde
         speed*25     — waktu vs baseline (targetWords × 26 dtk), dibobot accuracy
         wrong*15     — penalti salah+menyerah, dibobot accuracy
       Gate: accuracy < 30% → cap at 20
       Unlock: avg 3 sesi terakhir level ini ≥ 60 (inter) / ≥ 70 (expert) */
    spPerfHistory:        JSON.parse(localStorage.getItem('sp_perf') || '[]'),
    spUnlockedLevels:     @json($unlockedLevels),   /* server-side, per-akun */
    spNewLevelUnlocked:   null,   /* 'intermediate' | 'expert' | null */
    spDiffRecommendation: null,   /* { label, message, type } */
    correctCount: 0, wrongCount: 0, giveupCount: 0,

    /* jumlah kata per level kesulitan */
    wordsForLevel(lvl){
      return lvl === 'beginner' ? 5 : (lvl === 'intermediate' ? 10 : 20);
    },

    /* sesi sedang berjalan (mulai s/d sebelum selesai) */
    get inProgress(){ return this.started && !this.sessionDone; },

    answeredCount(){ return this.correctCount + this.wrongCount + this.giveupCount; },

    /* guard: cegah memilih level terkunci */
    onLevelChange() {
      if (this.level === 'intermediate' && !this.spUnlockedLevels.intermediate) this.level = 'beginner';
      if (this.level === 'expert'       && !this.spUnlockedLevels.expert)       this.level = 'beginner';
    },

    /* ── timer (identik Crossword) ── */
    startTimer() {
      this.t0 = Date.now();
      this.timerDisplay = '00:00';
      clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        const s = Math.floor((Date.now() - this.t0) / 1000);
        this.timerDisplay = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
      }, 1000);
    },
    stopTimer() { clearInterval(this.timerInterval); },

    /* ── inisialisasi sesi: ambil LD dari DDA lalu generate kata pertama ── */
    async start(){
      this.targetWords = this.wordsForLevel(this.level);
      this.roundIdx = 0;
      this.score = 0;
      this.started = true;
      this.sessionDone = false;
      this.answered = false;
      this.roundError = '';
      this.spNewLevelUnlocked = null;
      this.spDiffRecommendation = null;
      this.correctCount = 0;
      this.wrongCount = 0;
      this.giveupCount = 0;
      this.hintsUsed = 0;
      this.showGbaCard = false;

      // Fetch next-ld dari DDA hanya untuk Expert & Beyond
      if (this.level === 'expert') {
        try {
          const res = await fetch('{{ route("spelling.next-ld") }}');
          const data = await res.json();
          this.gbaLdCurrent = data.ld_next ?? 0.30;
          this.gbaLdNext    = this.gbaLdCurrent;
        } catch(e) {
          this.gbaLdCurrent = 0.30;
        }
      }

      this.startTimer();
      this.loadRound();
    },

    loadRound(){
      if (this.roundIdx >= this.targetWords){ this.finishSession(); return; }
      this.answer = '';
      this.feedback = '';
      this.roundError = '';
      this.answered = false;
      this.roundActive = true;
      this.loadingRound = true;
      fetch('{{ route('spelling.new') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({theme:this.theme, level:this.level, ld_target: this.level === 'expert' ? this.gbaLdCurrent : null})})
      .then(r=>r.json()).then(j=>{
        this.loadingRound = false;
        if(j.error){ this.roundError = j.error; this.roundActive = false; return; }
        this.clues = j.clues; this.wiki = j.wiki; this.wordAudio = j.wordAudio;
        this.speakWord();
        this.$nextTick(() => { this.$refs.answerInput?.focus(); });
      })
      .catch(()=>{ this.loadingRound = false; this.roundActive = false; this.roundError = 'Gagal memuat kata. Coba lagi.'; });
    },

    submit(){
      if (this.busy || !this.answer.trim()) return;
      this.busy = true;
      fetch('{{ route('spelling.answer') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({answer:this.answer})})
      .then(r=>r.json()).then(j=>{
        this.score += j.scoreDelta;
        if(j.correct) this.correctCount++; else this.wrongCount++;
        this.feedback = j.correct ? '✅ Correct!' : '❌ Incorrect. Answer: ' + j.expected;
        this.roundActive = false;
        this.answered = true;
      })
      .finally(()=>{ this.busy = false; });
    },

    giveup(){
      if (this.busy) return;
      this.busy = true;
      fetch('{{ route('spelling.answer') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({answer:'', giveup:true})})
      .then(r=>r.json()).then(j=>{
        this.giveupCount++;
        this.feedback = '🏳️ Surrender. Word: ' + j.expected;
        this.roundActive = false;
        this.answered = true;
      })
      .finally(()=>{ this.busy = false; });
    },

    /* ── transisi ke kata berikutnya (atau selesai bila kata terakhir) ── */
    nextRound(){
      this.roundIdx++;
      this.loadRound();   /* loadRound otomatis memanggil finishSession bila kata habis */
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
      this.roundActive = false;
      this.answered = false;
      this.sessionDone = true;
      const dur = Math.round((Date.now() - this.t0) / 1000);
      this.stopTimer();

      /* ══ RL perf_score (0–100) — accuracy + speed + wrong penalty ══ */
      const totalRounds  = this.answeredCount();
      const accuracy     = totalRounds > 0 ? (this.correctCount / totalRounds) : 0;
      const baseTime     = this.targetWords * 26;   /* baseline ~26 dtk/kata, skala ke jumlah kata */
      const rawSpeed     = Math.max(0, Math.min(25, 25 - ((dur - baseTime) / (baseTime * 2)) * 25));
      const speedScore   = rawSpeed * accuracy;
      const wrongPenalty = this.wrongCount + this.giveupCount * 2;
      const rawWrong     = Math.max(0, 15 - Math.min(15, wrongPenalty * 1.5));
      const wrongScore   = rawWrong * accuracy;
      let perfScore      = Math.round(accuracy * 60 + speedScore + wrongScore);
      if (accuracy < 0.30) perfScore = Math.min(perfScore, 20);

      /* Simpan ke history (max 20 entri) */
      this.spPerfHistory.push({ score: perfScore, level: this.level, ts: Date.now() });
      if (this.spPerfHistory.length > 20) this.spPerfHistory.shift();
      try { localStorage.setItem('sp_perf', JSON.stringify(this.spPerfHistory)); } catch(e) {}

      /* Unlock: rata-rata 3 sesi terakhir DI LEVEL INI */
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
        fetch('{{ route('spelling.unlock') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
          body: JSON.stringify({ level: justUnlocked })
        });
      }

      /* Recommendation message (3-level aware) */
      let rec;
      if (this.level === 'beginner') {
        if (justUnlocked === 'intermediate' || (levelAvg >= 60 && this.spUnlockedLevels.intermediate))
          rec = { label:'Advance to Intermediate!', message:'Great! Your Beginner average is '+levelAvg+'/100. Intermediate level is now unlocked!', type:'unlock' };
        else if (levelAvg >= 40)
          rec = { label:'Keep Practicing', message:'Good consistency. Reach an average of ≥ 60 to unlock Intermediate (current: '+levelAvg+'/100).', type:'progress' };
        else
          rec = { label:'Focus on Accuracy', message:'Focus on answering words correctly first. Target an average of ≥ 60 to unlock Intermediate.', type:'info' };
      } else if (this.level === 'intermediate') {
        if (justUnlocked === 'expert' || (levelAvg >= 70 && this.spUnlockedLevels.expert))
          rec = { label:'Advance to Expert & Beyond!', message:'Great! Your Intermediate average is '+levelAvg+'/100. Expert & Beyond is now unlocked!', type:'unlock' };
        else if (levelAvg >= 50)
          rec = { label:'Good Progress', message:'Keep improving! Reach an average of ≥ 70 to unlock Expert & Beyond (current: '+levelAvg+'/100).', type:'progress' };
        else
          rec = { label:'Maintain Intermediate', message:'Focus on speed and accuracy. Target an average of ≥ 70 to unlock Expert & Beyond.', type:'progress' };
      } else {
        if (levelAvg >= 75)
          rec = { label:'Expert & Beyond — Exceptional!', message:'Outstanding performance (avg '+levelAvg+'/100). GBA is actively tracking your ability!', type:'unlock' };
        else if (levelAvg >= 50)
          rec = { label:'Keep Going', message:'Good Expert & Beyond progress (avg '+levelAvg+'/100). GBA is adjusting difficulty automatically.', type:'progress' };
        else
          rec = { label:'Expert & Beyond — Focus', message:'GBA is measuring your ability. Focus on spelling accuracy to improve your score.', type:'info' };
      }
      this.spDiffRecommendation = rec;

      fetch('{{ route('spelling.finish') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({
          duration_sec:    dur,
          hints_used:      this.hintsUsed,
          hints_available: this.hintsAvailable,
        })
      }).then(r => r.json()).then(j => {
        if (j.theta !== undefined) {
          this.gbaTheta    = j.theta;
          this.gbaLdNext   = j.ld_next;
          this.showGbaCard = true;
        }
      }).catch(() => {});
    }
  }
}
</script>
</x-app-layout>
