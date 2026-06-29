<x-app-layout>
<div x-data="spellingGame()" class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50">
  <div class="max-w-7xl mx-auto p-6">

    {{-- ── Page title row ── --}}
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

      {{-- Timer + Score + FSM badge (visible setelah mulai) --}}
      <div x-show="started" style="margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap">

        {{-- FSM State badge --}}
        <div style="display:flex;align-items:center;gap:6px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:5px 12px">
          <span style="font-size:0.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8">FSM</span>
          <span :style="fsmBadgeStyle" x-text="fsmState"></span>
        </div>

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

    {{-- ══ Control bar ══ --}}
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

        <button @click="start()" :disabled="inProgress"
                class="ml-auto rounded-xl px-5 py-2.5 bg-sky-500 text-white font-semibold shadow hover:bg-sky-600 disabled:opacity-60 disabled:cursor-not-allowed"
                x-text="sessionDone ? '🔄 Try Again · ' + targetWords + ' words' : (started ? '...' : '🎮 Start · ' + wordsForLevel(level) + ' words')">
        </button>
      </div>

      {{-- Progress bar --}}
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

          {{-- IDLE --}}
          <template x-if="fsmState === 'IDLE'">
            <div class="text-center py-10">
              <div class="text-5xl mb-3">🐝</div>
              <h3 class="font-bold text-sky-900 text-lg mb-1">Spelling Bee</h3>
              <p class="text-sky-900/60 text-sm max-w-sm mx-auto">
                Listen to the word, read the clue, then type the correct spelling.
                Choose a theme &amp; difficulty, then click <b>Start</b> to begin.
              </p>
            </div>
          </template>

          {{-- LOADING --}}
          <template x-if="fsmState === 'LOADING'">
            <div class="text-center py-12">
              <div class="text-4xl mb-3 animate-pulse">🔄</div>
              <p class="text-sky-900/70 font-medium">Preparing word…</p>
            </div>
          </template>

          {{-- LISTENING --}}
          <template x-if="fsmState === 'LISTENING'">
            <div>
              <div class="flex flex-wrap gap-2 mb-4 items-center">
                <button @click="speakWord()"
                        class="rounded-xl px-3 py-2 border bg-yellow-50 text-amber-700 hover:bg-yellow-100">
                  🔊 Listen
                </button>
                <button @click="rec()"
                        class="rounded-xl px-4 py-2.5 border border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700 hover:bg-fuchsia-100 flex items-center gap-2">
                  🎤 Voice
                </button>
                {{-- Countdown pill --}}
                <div class="ml-auto flex items-center gap-2 rounded-xl px-3 py-1.5 border"
                     :class="wordTimeLeft <= 10 ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200'">
                  <svg style="width:13px;height:13px" :style="wordTimeLeft<=10?'color:#e11d48':'color:#64748b'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span class="font-mono text-sm font-bold"
                        :class="wordTimeLeft<=10?'text-rose-600':'text-slate-600'"
                        x-text="wordTimeLeft + 's'"></span>
                </div>
              </div>

              <h3 class="font-bold text-sky-900 mb-2 flex items-center gap-2">💡 Clues</h3>
              <ul class="list-disc ml-5 text-sky-900/80 space-y-1">
                <template x-for="c in clues"><li x-text="c"></li></template>
              </ul>

              <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <input x-model="answer" placeholder="Ketik ejaan kata…"
                       class="w-full sm:flex-1 rounded-xl border px-3 py-2 focus:ring-sky-300 focus:border-sky-400 bg-white/90"
                       @keyup.enter="submit()" x-ref="answerInput">
                <div class="flex gap-2">
                  <button @click="submit()" :disabled="!answer.trim()"
                          class="flex-1 sm:flex-none justify-center items-center flex rounded-xl px-4 py-2 bg-emerald-500 text-white font-semibold hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    ✅ Answer
                  </button>
                  <button @click="giveup()"
                          class="flex-1 sm:flex-none justify-center items-center flex rounded-xl px-4 py-2 bg-rose-500 text-white hover:bg-rose-600">
                    🏳️ Surrender
                  </button>
                </div>
              </div>
            </div>
          </template>

          {{-- EVALUATING --}}
          <template x-if="fsmState === 'EVALUATING'">
            <div class="text-center py-12">
              <div class="text-4xl mb-3 animate-pulse">⏳</div>
              <p class="text-sky-900/70 font-medium">Checking answer…</p>
            </div>
          </template>

          {{-- FEEDBACK --}}
          <template x-if="fsmState === 'FEEDBACK'">
            <div class="text-center py-6">
              <div class="mb-5 text-lg font-semibold"
                   :class="{
                     'text-emerald-700': feedback.startsWith('✅'),
                     'text-rose-700':    feedback.startsWith('❌'),
                     'text-amber-700':   feedback.startsWith('🏳️'),
                     'text-orange-600':  feedback.startsWith('⏱️')
                   }"
                   x-text="feedback"></div>
              <button @click="nextRound()"
                      class="rounded-xl px-6 py-3 bg-sky-500 text-white font-semibold hover:bg-sky-600 shadow-lg"
                      x-text="(roundIdx + 1 >= targetWords) ? '🏁 View Results' : '➡️ Next Word'">
              </button>
            </div>
          </template>

          {{-- ERROR --}}
          <template x-if="fsmState === 'ERROR'">
            <div class="text-center py-8">
              <p class="text-rose-600 font-medium mb-3" x-text="roundError"></p>
              <div class="flex justify-center gap-2">
                <button @click="retryLoad()"
                        class="rounded-xl px-5 py-2 bg-sky-500 text-white hover:bg-sky-600">
                  🔄 Retry
                </button>
                <button @click="fsm('RESET')"
                        class="rounded-xl px-5 py-2 bg-slate-200 text-slate-700 hover:bg-slate-300">
                  ↩ Back
                </button>
              </div>
            </div>
          </template>
        </div>

        {{-- ── FSM Transition Log ── --}}
        <div x-show="fsmHistory.length > 0" class="rounded-2xl bg-white/90 backdrop-blur border shadow overflow-hidden">
          <details>
            <summary class="px-5 py-3 cursor-pointer select-none flex items-center gap-2 hover:bg-slate-50">
              <span style="font-size:0.8rem">⚙️</span>
              <span style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b">FSM Transition Log</span>
              <span style="margin-left:auto;font-size:0.68rem;color:#94a3b8" x-text="'(' + fsmHistory.length + ' transitions)'"></span>
            </summary>
            <div class="px-5 pb-4">
              <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:8px">Newest first</div>
              <template x-for="(e, i) in fsmHistory" :key="i">
                <div style="display:flex;align-items:center;gap:7px;padding:5px 0;border-bottom:1px solid #f1f5f9;flex-wrap:wrap">
                  <span style="font-family:monospace;font-size:0.65rem;color:#94a3b8;min-width:56px" x-text="e.time"></span>
                  <span style="background:#f1f5f9;color:#475569;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:5px" x-text="e.from"></span>
                  <svg style="width:11px;height:11px;color:#94a3b8;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                  <span style="background:#ede9fe;color:#5b21b6;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:5px;font-style:italic" x-text="e.event"></span>
                  <svg style="width:11px;height:11px;color:#94a3b8;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                  <span style="background:#dbeafe;color:#1e40af;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:5px" x-text="e.to"></span>
                </div>
              </template>
            </div>
          </details>
        </div>
      </div>

      {{-- ══ Right: explanation + results ══ --}}
      <div class="space-y-4">
        <template x-if="answered && wiki.extract">
          <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
            <h3 class="font-bold text-sky-900">📖 Explanation</h3>
            <p class="text-sky-900/80 mt-1 text-sm leading-relaxed" x-text="wiki.extract"></p>
            <img :src="wiki.image" x-show="wiki.image" class="mt-3 rounded-xl shadow" alt="">
          </div>
        </template>

        {{-- ══ Unified Result Card ══ --}}
        <template x-if="sessionDone">
          <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">

            <div class="flex items-center gap-3 mb-4">
              <span class="text-2xl">🎉</span>
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hasil Sesi</p>
                <p class="font-bold text-slate-800">Sesi selesai!</p>
              </div>
            </div>

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

            <a :href="'{{ route('leaderboard.index') }}?game=spelling-bee&level=' + level"
               class="inline-block px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
              🏆 Leaderboard
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
    targetWords: 5,
    roundIdx: 0, score: 0, clues: [], wiki: {}, wordAudio: '',
    answer: '', feedback: '', _roundError: '',
    t0: null, timerDisplay: '00:00', timerInterval: null,
    wordTimerId: null, wordTimeLeft: 0, wordTimeoutSec: 60,

    /* ══════════════════════════════════════════════════════════════
       FSM — Finite State Machine
       ┌─────────────┬──────────────┬────────────────────────────────┐
       │ FROM State  │ Event        │ TO State                       │
       ├─────────────┼──────────────┼────────────────────────────────┤
       │ IDLE        │ START        │ LOADING                        │
       │ LOADING     │ WORD_READY   │ LISTENING                      │
       │ LOADING     │ LOAD_FAIL    │ ERROR                          │
       │ LOADING     │ ALL_DONE     │ FINISHED                       │
       │ ERROR       │ RETRY        │ LOADING                        │
       │ ERROR       │ RESET        │ IDLE                           │
       │ LISTENING   │ SUBMIT       │ EVALUATING                     │
       │ LISTENING   │ GIVEUP       │ EVALUATING                     │
       │ LISTENING   │ TIMEOUT      │ FEEDBACK                       │
       │ EVALUATING  │ RESULT       │ FEEDBACK                       │
       │ EVALUATING  │ EVAL_FAIL    │ LISTENING                      │
       │ FEEDBACK    │ NEXT         │ LOADING                        │
       │ FEEDBACK    │ FINISH       │ FINISHED                       │
       │ FINISHED    │ RESTART      │ IDLE                           │
       └─────────────┴──────────────┴────────────────────────────────┘
    ══════════════════════════════════════════════════════════════ */
    fsmState: 'IDLE',
    fsmHistory: [],

    FSM_TRANSITIONS: {
      IDLE:       { START:      'LOADING'                             },
      LOADING:    { WORD_READY: 'LISTENING', LOAD_FAIL: 'ERROR', ALL_DONE: 'FINISHED' },
      ERROR:      { RETRY:      'LOADING',   RESET:     'IDLE'        },
      LISTENING:  { SUBMIT:     'EVALUATING', GIVEUP:   'EVALUATING', TIMEOUT: 'FEEDBACK' },
      EVALUATING: { RESULT:     'FEEDBACK',  EVAL_FAIL: 'LISTENING'   },
      FEEDBACK:   { NEXT:       'LOADING',   FINISH:    'FINISHED'    },
      FINISHED:   { RESTART:    'IDLE'                                },
    },

    /* Send an event to the FSM — returns true if transition was valid */
    fsm(event) {
      const trans = this.FSM_TRANSITIONS[this.fsmState];
      if (!trans || !trans[event]) {
        console.warn(`[FSM] Blocked: "${this.fsmState}" + "${event}" → no target`);
        return false;
      }
      const prev = this.fsmState;
      this.fsmState = trans[event];
      this.fsmHistory = [
        { from: prev, event, to: this.fsmState, time: new Date().toLocaleTimeString() },
        ...this.fsmHistory,
      ].slice(0, 10);
      console.log(`%c[FSM] ${prev} ──[${event}]──▶ ${this.fsmState}`, 'color:#6366f1;font-weight:bold');
      return true;
    },

    /* ── Computed state flags — all driven by fsmState ── */
    get started()     { return this.fsmState !== 'IDLE'; },
    get loadingRound(){ return this.fsmState === 'LOADING'; },
    get roundActive() { return this.fsmState === 'LISTENING'; },
    get busy()        { return this.fsmState === 'EVALUATING'; },
    get answered()    { return this.fsmState === 'FEEDBACK'; },
    get sessionDone() { return this.fsmState === 'FINISHED'; },
    get roundError()  { return this.fsmState === 'ERROR' ? this._roundError : ''; },
    get inProgress()  { return !['IDLE', 'FINISHED'].includes(this.fsmState); },

    /* Visual badge style per state */
    get fsmBadgeStyle() {
      const map = {
        IDLE:       'background:#e2e8f0;color:#475569',
        LOADING:    'background:#dbeafe;color:#1e40af',
        LISTENING:  'background:#fef3c7;color:#78350f',
        EVALUATING: 'background:#ede9fe;color:#4c1d95',
        FEEDBACK:   'background:#d1fae5;color:#065f46',
        FINISHED:   'background:#dcfce7;color:#14532d',
        ERROR:      'background:#fee2e2;color:#991b1b',
      };
      const base = map[this.fsmState] || map.IDLE;
      return base + ';font-size:0.7rem;font-weight:700;padding:2px 10px;border-radius:6px;letter-spacing:.04em';
    },

    /* GBA/DDA */
    gbaTheta: 0, gbaLdNext: 0.30, gbaLdCurrent: 0.30,
    hintsUsed: 0, hintsAvailable: 3, showGbaCard: false,

    /* RL Adaptive Difficulty */
    spPerfHistory:        JSON.parse(localStorage.getItem('sp_perf') || '[]'),
    spUnlockedLevels:     @json($unlockedLevels),
    spNewLevelUnlocked:   null,
    spDiffRecommendation: null,
    correctCount: 0, wrongCount: 0, giveupCount: 0,

    wordsForLevel(lvl){ return lvl === 'beginner' ? 5 : (lvl === 'intermediate' ? 10 : 20); },
    answeredCount(){ return this.correctCount + this.wrongCount + this.giveupCount; },

    onLevelChange(){
      if (this.level === 'intermediate' && !this.spUnlockedLevels.intermediate) this.level = 'beginner';
      if (this.level === 'expert'       && !this.spUnlockedLevels.expert)       this.level = 'beginner';
    },

    startTimer(){
      this.t0 = Date.now(); this.timerDisplay = '00:00';
      clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        const s = Math.floor((Date.now() - this.t0) / 1000);
        this.timerDisplay = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
      }, 1000);
    },
    stopTimer(){ clearInterval(this.timerInterval); },

    /* ── retryLoad: encapsulates RETRY transition + reload ── */
    retryLoad(){
      if (!this.fsm('RETRY')) return;  /* ERROR → LOADING */
      this.loadRound();
    },

    /* ── Word-level countdown timer (per round) ── */
    startWordTimer(){
      /* Timeout per level: Beginner 60s, Intermediate 45s, Expert 30s */
      this.wordTimeoutSec = this.level === 'beginner' ? 60 : (this.level === 'intermediate' ? 45 : 30);
      this.wordTimeLeft   = this.wordTimeoutSec;
      clearInterval(this.wordTimerId);
      this.wordTimerId = setInterval(() => {
        this.wordTimeLeft--;
        if (this.wordTimeLeft <= 0) {
          clearInterval(this.wordTimerId);
          this.handleTimeout();
        }
      }, 1000);
    },

    clearWordTimer(){
      clearInterval(this.wordTimerId);
      this.wordTimeLeft = 0;
    },

    /* ── TIMEOUT handler: auto-giveup when countdown expires ── */
    handleTimeout(){
      if (!this.fsm('TIMEOUT')) return;  /* LISTENING → FEEDBACK */
      this.clearWordTimer();
      this.giveupCount++;
      this.feedback = '⏱️ Time\'s up!';
      /* Fire-and-forget: update feedback with expected word from server */
      fetch('{{ route('spelling.answer') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer: '', giveup: true })
      })
      .then(r => r.json())
      .then(j => { this.feedback = '⏱️ Time\'s up! Word: ' + j.expected; })
      .catch(() => {});
    },

    /* ── Start / Restart session ── */
    async start(){
      /* Support restart: if already FINISHED, log RESTART transition */
      if (this.fsmState === 'FINISHED') {
        this.fsm('RESTART');   /* FINISHED → IDLE */
      } else if (this.fsmState !== 'IDLE') {
        console.warn('[FSM] start() hanya bisa dipanggil dari IDLE atau FINISHED');
    return;
      }
      if (!this.fsm('START')) return;  /* IDLE → LOADING */

      this.targetWords = this.wordsForLevel(this.level);
      this.roundIdx = 0; this.score = 0;
      this.answer = ''; this.feedback = ''; this._roundError = '';
      this.spNewLevelUnlocked = null; this.spDiffRecommendation = null;
      this.correctCount = 0; this.wrongCount = 0; this.giveupCount = 0;
      this.hintsUsed = 0; this.showGbaCard = false; this.wiki = {};
      this.clearWordTimer();

      if (this.level === 'expert') {
        try {
          const r = await fetch('{{ route("spelling.next-ld") }}');
          const d = await r.json();
          this.gbaLdCurrent = d.ld_next ?? 0.30;
          this.gbaLdNext    = this.gbaLdCurrent;
        } catch { this.gbaLdCurrent = 0.30; }
      }

      this.startTimer();
      this.loadRound();
    },

    /* ── Load next word from server ── */
    loadRound(){
      if (this.roundIdx >= this.targetWords){
        this.fsm('ALL_DONE');  /* LOADING → FINISHED */
        this.finishSession();
        return;
      }
      this.answer = ''; this.feedback = ''; this._roundError = '';
      /* fsmState is LOADING here — no extra transition needed */

      fetch('{{ route('spelling.new') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme: this.theme, level: this.level, ld_target: this.level === 'expert' ? this.gbaLdCurrent : null })
      })
      .then(r => r.json())
      .then(j => {
        if (j.error) {
          this._roundError = j.error;
          this.fsm('LOAD_FAIL');  /* LOADING → ERROR */
          return;
        }
        this.clues = j.clues; this.wiki = j.wiki; this.wordAudio = j.wordAudio;
        this.fsm('WORD_READY');   /* LOADING → LISTENING */
        this.startWordTimer();    /* start per-word countdown */
        this.speakWord();
        this.$nextTick(() => { this.$refs.answerInput?.focus(); });
      })
      .catch(() => {
        this._roundError = 'Gagal memuat kata. Coba lagi.';
        this.fsm('LOAD_FAIL');    /* LOADING → ERROR */
      });
    },

    /* ── Submit answer ── */
    submit(){
      if (!this.answer.trim()) return;
      this.clearWordTimer();
      if (!this.fsm('SUBMIT')) return;    /* LISTENING → EVALUATING */

      fetch('{{ route('spelling.answer') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer: this.answer })
      })
      .then(r => r.json())
      .then(j => {
        this.score += j.scoreDelta;
        if (j.correct) this.correctCount++; else this.wrongCount++;
        this.feedback = j.correct ? '✅ Correct!' : '❌ Incorrect. Answer: ' + j.expected;
        this.fsm('RESULT');               /* EVALUATING → FEEDBACK */
      })
      .catch(() => { this.fsm('EVAL_FAIL'); });  /* EVALUATING → LISTENING */
    },

    /* ── Give up ── */
    giveup(){
      this.clearWordTimer();
      if (!this.fsm('GIVEUP')) return;    /* LISTENING → EVALUATING */

      fetch('{{ route('spelling.answer') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer: '', giveup: true })
      })
      .then(r => r.json())
      .then(j => {
        this.giveupCount++;
        this.feedback = '🏳️ Surrender. Word: ' + j.expected;
        this.fsm('RESULT');               /* EVALUATING → FEEDBACK */
      })
      .catch(() => { this.fsm('EVAL_FAIL'); });  /* EVALUATING → LISTENING */
    },

    /* ── Advance to next word ── */
    nextRound(){
      this.roundIdx++;
      if (this.roundIdx >= this.targetWords) {
        this.fsm('FINISH');  /* FEEDBACK → FINISHED */
        this.finishSession();
      } else {
        this.fsm('NEXT');    /* FEEDBACK → LOADING */
        this.loadRound();
      }
    },

    speakWord(){
      if (!this.wordAudio) return;
      const u = new SpeechSynthesisUtterance(this.wordAudio);
      u.lang = 'en-GB'; speechSynthesis.speak(u);
    },

    rec(){
      try {
        const R = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!R) { alert('Browser tidak mendukung voice recognition'); return; }
        const r = new R(); r.lang = 'en-GB';
        r.onresult = (e) => { this.answer = e.results[0][0].transcript.replace(/\s+/g, '').toLowerCase(); };
        r.start();
      } catch(e) { console.log(e); }
    },

    /* ── Finish session — fsmState is already FINISHED ── */
    finishSession(){
      const dur = Math.round((Date.now() - this.t0) / 1000);
      this.stopTimer();

      /* RL perf_score */
      const total = this.answeredCount();
      const acc   = total > 0 ? this.correctCount / total : 0;
      const base  = this.targetWords * 26;
      const spd   = Math.max(0, Math.min(25, 25 - ((dur - base) / (base * 2)) * 25)) * acc;
      const pen   = Math.max(0, 15 - Math.min(15, (this.wrongCount + this.giveupCount * 2) * 1.5)) * acc;
      let perf    = Math.round(acc * 60 + spd + pen);
      if (acc < 0.30) perf = Math.min(perf, 20);

      this.spPerfHistory.push({ score: perf, level: this.level, ts: Date.now() });
      if (this.spPerfHistory.length > 20) this.spPerfHistory.shift();
      try { localStorage.setItem('sp_perf', JSON.stringify(this.spPerfHistory)); } catch {}

      const same = this.spPerfHistory.filter(h => h.level === this.level).slice(-3);
      const avg  = same.length ? Math.round(same.reduce((s, x) => s + x.score, 0) / same.length) : 0;

      let unlocked = null;
      if (this.level === 'beginner' && avg >= 60 && !this.spUnlockedLevels.intermediate) {
        this.spUnlockedLevels = { ...this.spUnlockedLevels, intermediate: true };
        unlocked = 'intermediate';
      } else if (this.level === 'intermediate' && avg >= 70 && !this.spUnlockedLevels.expert) {
        this.spUnlockedLevels = { ...this.spUnlockedLevels, expert: true };
        unlocked = 'expert';
      }
      if (unlocked) {
        this.spNewLevelUnlocked = unlocked;
        fetch('{{ route('spelling.unlock') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
          body: JSON.stringify({ level: unlocked })
        });
      }

      const L = this.level, U = this.spUnlockedLevels;
      let rec;
      if (L === 'beginner') {
        if (unlocked === 'intermediate' || (avg >= 60 && U.intermediate))
          rec = { label:'Advance to Intermediate!', message:'Great! Beginner avg '+avg+'/100. Intermediate level is now unlocked!', type:'unlock' };
        else if (avg >= 40)
          rec = { label:'Keep Practicing', message:'Good consistency. Reach avg ≥ 60 to unlock Intermediate (current: '+avg+'/100).', type:'progress' };
        else
          rec = { label:'Focus on Accuracy', message:'Focus on answering correctly. Target avg ≥ 60 to unlock Intermediate.', type:'info' };
      } else if (L === 'intermediate') {
        if (unlocked === 'expert' || (avg >= 70 && U.expert))
          rec = { label:'Advance to Expert!', message:'Great! Intermediate avg '+avg+'/100. Expert & Beyond is now unlocked!', type:'unlock' };
        else if (avg >= 50)
          rec = { label:'Good Progress', message:'Reach avg ≥ 70 to unlock Expert & Beyond (current: '+avg+'/100).', type:'progress' };
        else
          rec = { label:'Maintain Intermediate', message:'Target avg ≥ 70 to unlock Expert & Beyond.', type:'progress' };
      } else {
        if (avg >= 75)
          rec = { label:'Expert — Exceptional!', message:'Outstanding Expert performance (avg '+avg+'/100). GBA is tracking your ability!', type:'unlock' };
        else if (avg >= 50)
          rec = { label:'Keep Going', message:'Good Expert progress (avg '+avg+'/100). GBA adjusting difficulty automatically.', type:'progress' };
        else
          rec = { label:'Expert — Focus', message:'GBA measuring your ability. Focus on spelling accuracy to improve.', type:'info' };
      }
      this.spDiffRecommendation = rec;

      fetch('{{ route('spelling.finish') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ duration_sec: dur, hints_used: this.hintsUsed, hints_available: this.hintsAvailable })
      })
      .then(r => r.json())
      .then(j => {
        if (j.theta !== undefined) { this.gbaTheta = j.theta; this.gbaLdNext = j.ld_next; this.showGbaCard = true; }
      })
      .catch(() => {});
    }
  }
}
</script>
</x-app-layout>
