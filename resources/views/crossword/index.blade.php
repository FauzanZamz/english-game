<x-app-layout>

{{-- ── Extra head assets ── --}}
@push('styles')
<link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet">
@endpush

<style>
/* ═══════════════════════════════════════════════
   CROSSWORD — REDESIGNED (v2)
   Fixes:
   • Light-background grid so cells are always visible
   • Native-select fix (dark text on white bg)
   • Arrow-key direction corrected for flipped grid
   • Proper gap/border visibility
   • All text legible regardless of theme
═══════════════════════════════════════════════ */

.cw-wrap { font-family: 'Poppins', 'Segoe UI', sans-serif; }

/* ── Grid outer shell ── */
#cw-grid {
  display: inline-grid;
  background: transparent;   /* no gap lines — black squares are invisible */
  padding: 3px;
  gap: 3px;
  border-radius: 10px;
}

/* ── Individual cell ── */
.cw-cell {
  display: block;
  width: 2.4rem; height: 2.4rem;
  text-align: center; line-height: 2.4rem;
  font-family: inherit;
  font-size: 0.875rem; font-weight: 700;
  text-transform: uppercase;
  border: none; outline: none;
  border-radius: 5px;
  padding: 0;
  caret-color: transparent;
  transition: background 0.1s, box-shadow 0.1s, color 0.1s;
  cursor: pointer;
  user-select: none;
  /* default: visible playable cell */
  background: #f8faff;
  color: #1e293b;
  border: 2px solid #c7d2fe !important;
  box-shadow: 0 3px 8px rgba(99,102,241,0.10), 0 1px 3px rgba(0,0,0,0.08);
}
/* black square — fully invisible */
.cw-cell:disabled,
.cw-cell:disabled.active-word,
.cw-cell:disabled.active-cursor,
.cw-cell:disabled.cell-correct,
.cw-cell:disabled.cell-incorrect {
  background: transparent !important;
  color:       transparent !important;
  box-shadow:  none        !important;
  border:      none        !important;
  cursor:      default;
  pointer-events: none;
}
/* highlighted word — only applies when NOT disabled */
.cw-cell:not(:disabled).active-word {
  background: #dbeafe;
  color: #1d4ed8;
}
/* cursor position — only applies when NOT disabled */
.cw-cell:not(:disabled).active-cursor {
  background: #fef08a;
  color: #713f12;
  box-shadow: 0 0 0 2px #f59e0b;
}
/* post-submit: correct */
.cw-cell:not(:disabled).cell-correct {
  background: #bbf7d0;
  color: #14532d;
  box-shadow: 0 0 0 2px #22c55e;
}
/* post-submit: incorrect */
.cw-cell:not(:disabled).cell-incorrect {
  background: #fecaca;
  color: #7f1d1d;
  box-shadow: 0 0 0 2px #ef4444;
}
.cw-cell:focus { outline: none; }

/* ── Hint letter: pre-filled scattered letter ── */
.cw-cell.hint-letter {
  background: #ede9fe !important;     /* soft violet */
  color: #4f46e5 !important;
  border-color: #a5b4fc !important;
  font-style: italic;
  cursor: default !important;
  pointer-events: none;               /* cannot be edited */
}
/* ── RL unlock: "Go Expert" button glow ── */
@keyframes expertGlow {
  0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
  50%      { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
}
.btn-unlock-expert {
  background: linear-gradient(135deg,#ef4444,#f97316);
  color:#fff; animation: expertGlow 1.8s ease infinite;
}
.btn-unlock-expert:hover { background: linear-gradient(135deg,#dc2626,#ea580c); }

/* ── Cell number badge ── */
.cw-num {
  position: absolute;
  top: 2px; left: 3px;
  font-size: 0.42rem;
  font-weight: 700;
  color: #64748b;
  line-height: 1;
  pointer-events: none;
  z-index: 1;
}

/* ── Select fix: always dark text ── */
.cw-select {
  appearance: none;
  background: #fff;
  color: #1e293b;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  font-family: inherit;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.65rem center;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cw-select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.cw-select option { color: #1e293b; background: #fff; }

/* ── Buttons ── */
.btn-primary {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 0.55rem 1.25rem;
  border-radius: 10px; border: none;
  font-family: inherit; font-size: 0.875rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, transform 0.1s, opacity 0.15s;
}
.btn-primary:active { transform: scale(0.97); }
.btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-indigo  { background: #6366f1; color: #fff; }
.btn-indigo:hover:not(:disabled)  { background: #4f46e5; }
.btn-green   { background: #22c55e; color: #fff; }
.btn-green:hover:not(:disabled)   { background: #16a34a; }
.btn-ghost   { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-ghost:hover:not(:disabled)   { background: #e2e8f0; }

/* ── Cards ── */
.cw-card {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

/* ── Clue list scroll ── */
.clue-scroll { max-height: 390px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
.clue-item {
  padding: 0.45rem 0.6rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.8rem;
  line-height: 1.45;
  color: #334155;
  transition: background 0.1s;
  border-left: 3px solid transparent;
}
.clue-item:hover { background: #f8fafc; }
.clue-item.clue-across-active { background: #eff6ff; border-left-color: #3b82f6; color: #1d4ed8; }
.clue-item.clue-down-active   { background: #f0fdf4; border-left-color: #22c55e; color: #15803d; }

/* ── Progress bar ── */
.progress-track {
  height: 7px; border-radius: 99px;
  background: #e2e8f0; overflow: hidden;
}
.progress-fill {
  height: 100%; border-radius: 99px;
  background: linear-gradient(90deg, #6366f1, #22c55e);
  transition: width 0.4s ease;
}

/* ── Result banner ── */
.result-banner {
  border-radius: 12px; padding: 1rem 1.25rem;
  font-size: 0.9rem; font-weight: 600;
  display: flex; align-items: center; gap: 10px;
  animation: fadeSlideIn 0.3s ease;
}
.result-banner.success { background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; }
.result-banner.partial { background: #fffbeb; border: 1.5px solid #fcd34d; color: #92400e; }

/* ── Score pop ── */
@keyframes scorePop {
  0%   { transform: scale(1); }
  45%  { transform: scale(1.5); color: #22c55e; }
  100% { transform: scale(1); }
}
.score-pop { animation: scorePop 0.4s ease; }

@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(5px); }
  to   { opacity: 1; transform: translateY(0); }
}
.fade-in { animation: fadeSlideIn 0.3s ease; }

/* ── Spinner ── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; display: inline-block; }

/* ── Label ── */
.field-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 5px; }

/* ══════════════════════════════
   MOBILE RESPONSIVE ≤ 640px
══════════════════════════════ */
.cw-mobile-clues { display: none; }

@media (max-width: 640px) {
  /* smaller cells */
  .cw-cell {
    width: 1.9rem !important; height: 1.9rem !important;
    font-size: 0.78rem !important; line-height: 1.9rem !important;
    border-radius: 4px !important;
  }
  #cw-grid > div { width: 1.9rem !important; height: 1.9rem !important; }
  #cw-grid { gap: 2px !important; padding: 2px !important; }

  /* single-column layout */
  .cw-main-layout { grid-template-columns: 1fr !important; }

  /* hide desktop sidebar, show mobile clue panel */
  .cw-sidebar { display: none !important; }
  .cw-mobile-clues { display: block !important; }

  /* controls: stack selects, keep buttons on one row */
  .cw-controls-row { flex-direction: column !important; align-items: stretch !important; gap: 10px !important; }
  .cw-controls-selects { flex-direction: column !important; }
  .cw-select { width: 100% !important; min-width: unset !important; }
  .cw-controls-btns { margin-left: 0 !important; justify-content: flex-end; }

  /* active clue strip: stack vertically */
  .cw-active-clue-strip { flex-direction: column !important; }

  /* buttons slightly smaller */
  .btn-primary { padding: 0.5rem 0.9rem !important; font-size: 0.8rem !important; }

  /* header: wrap timer + score under title */
  .cw-header-row { flex-wrap: wrap !important; gap: 8px !important; }
}


/* ── AI: Adaptive difficulty badge ── */
.diff-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 8px;
  font-size: 0.75rem; font-weight: 700;
  border: 1.5px solid; animation: fadeSlideIn 0.3s ease;
}
.diff-easy   { background: #f0fdf4; color: #166534; border-color: #86efac; }
.diff-medium { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
.diff-hard   { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }

/* ── AI: Bayes warning cell ── */
.cw-cell:not(:disabled).bayes-warn {
  box-shadow: 0 0 0 2px #f59e0b, inset 0 0 0 1px #fef3c7;
  background: #fffbeb; color: #78350f;
}
</style>

{{-- ════════════════════════════════════════
     PAGE
════════════════════════════════════════ --}}
<div x-data="crossword()" class="cw-wrap min-h-[calc(100vh-4rem)] bg-gradient-to-br from-slate-50 to-indigo-50/60">
  <div class="max-w-7xl mx-auto px-4 py-7 sm:px-6 lg:px-8">

    {{-- ── Page title row ── --}}
    <div class="cw-header-row flex flex-wrap items-center gap-3 mb-6">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center">
          <span style="font-size:1.15rem">🧩</span>
        </div>
        <div>
          <h1 style="margin:0;font-size:1.2rem;font-weight:800;color:#1e293b;line-height:1.2">Crossword</h1>
          <p style="margin:0;font-size:0.7rem;color:#94a3b8;font-weight:500">Pick a theme, generate, and solve!</p>
        </div>
      </div>

      <div style="margin-left:auto;display:flex;align-items:center;gap:10px">
        {{-- Timer pill --}}
        <div style="display:flex;align-items:center;gap:7px;background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:10px;padding:6px 14px">
          <svg style="width:15px;height:15px;color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span style="font-family:monospace;font-size:0.85rem;font-weight:700;color:#1e293b" x-text="timerDisplay">00:00</span>
        </div>
        {{-- Score pill --}}
        <div style="display:flex;align-items:center;gap:7px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:6px 14px">
          <span style="font-size:0.72rem;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.05em">Score</span>
          <span style="font-size:1rem;font-weight:800;color:#166534" x-text="score" :class="{'score-pop': scorePop}">0</span>
        </div>
      </div>
    </div>

    {{-- ── Controls card ── --}}
    <div class="cw-card p-4 mb-5">
      <div class="cw-controls-row" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px">
        <div>
          <p class="field-label">Theme</p>
          <select x-model="theme" class="cw-select" style="min-width:160px">
            @foreach($themes as $t)
              <option value="{{ $t->slug }}">{{ $t->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <p class="field-label">Difficulty</p>
          <select x-model="level" class="cw-select" style="min-width:170px">
            <option value="beginner">🌱 Beginner — 12×12</option>
            <option value="expert">🔥 Expert — 15×15</option>
          </select>
        </div>

        <div style="margin-left:auto;display:flex;align-items:center;gap:8px">
          <button @click="clearGrid()" x-show="grid.length" class="btn-primary btn-ghost">
            🗑 Clear
          </button>
          <button @click="generate()" :disabled="loading" class="btn-primary btn-indigo">
            <span x-show="loading" class="spin" style="font-size:0.9rem">⟳</span>
            <span x-text="loading ? 'Generating…' : '🧩 Generate'"></span>
          </button>
          <button @click="submitPuzzle()" x-show="grid.length && !submitted" class="btn-primary btn-green">
            ✅ Submit
          </button>
          <button @click="submitted=false; clearGrid();" x-show="submitted" class="btn-primary btn-ghost">
            🔄 Try Again
          </button>
          {{-- RL unlock: appears after RL recommends Expert --}}
          <button x-show="expertUnlocked && level==='beginner'"
                  @click="level='expert'; expertUnlocked=false; generate();"
                  class="btn-primary btn-unlock-expert"
                  title="AI mendeteksi kamu siap naik ke Expert!">
            🔥 Go Expert!
          </button>
        </div>
      </div>
    </div>

    {{-- Error --}}
    <template x-if="error">
      <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:12px 16px;color:#991b1b;font-size:0.85rem;margin-bottom:16px" class="fade-in" x-text="error"></div>
    </template>

    {{-- Result banner --}}
    <template x-if="resultMsg">
      <div class="result-banner mb-3" :class="resultPerfect ? 'success' : 'partial'">
        <span style="font-size:1.3rem" x-text="resultPerfect ? '🏆' : '📝'"></span>
        <span x-text="resultMsg"></span>
      </div>
    </template>

    {{-- AI Feature 2: Adaptive Difficulty Recommendation --}}
    <template x-if="diffRecommendation">
      <div style="margin-bottom:16px;padding:12px 16px;background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;display:flex;align-items:center;gap:12px;animation:fadeSlideIn 0.4s ease">
        <span style="font-size:1.1rem">🧠</span>
        <div style="flex:1">
          <p style="margin:0 0 3px;font-size:0.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Adaptive Difficulty AI</p>
          <p style="margin:0;font-size:0.84rem;color:#334155" x-text="diffRecommendation.message"></p>
        </div>
        <span class="diff-badge" :class="diffRecommendation.badgeClass" x-text="diffRecommendation.label"></span>
      </div>
    </template>

    {{-- ── Empty state ── --}}
    <template x-if="!grid.length && !loading">
      <div class="cw-card" style="padding:3.5rem 2rem;text-align:center">
        <div style="font-size:3rem;margin-bottom:12px">🧩</div>
        <p style="font-size:1rem;font-weight:600;color:#475569;margin:0 0 6px">No puzzle yet</p>
        <p style="font-size:0.85rem;color:#94a3b8;margin:0">Choose a theme and difficulty, then hit <strong style="color:#6366f1">Generate</strong>.</p>
      </div>
    </template>

    {{-- ── Main layout ── --}}
    <template x-if="grid.length">
      <div class="cw-main-layout fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start">

        {{-- LEFT: Grid panel --}}
        <div class="cw-card p-5">
          {{-- Progress bar --}}
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
            <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8">Progress</span>
            <div class="progress-track" style="flex:1">
              <div class="progress-fill" :style="`width:${progressPct}%`"></div>
            </div>
            <span style="font-size:0.75rem;font-weight:700;color:#64748b;font-family:monospace" x-text="`${filledCount}/${totalCells}`"></span>
          </div>

          {{-- Grid (scrollable on small screens) --}}
          <div style="overflow-x:auto;padding-bottom:4px;background:transparent;display:flex;justify-content:center;align-items:center;min-height:300px">
            <div id="cw-grid" :style="`grid-template-columns: repeat(${size}, 2.4rem);`">
              <template x-for="(row, r) in grid" :key="'r'+r">
                <template x-for="(cell, c) in row" :key="'c'+c">
                  <div style="position:relative;width:2.4rem;height:2.4rem">
                    <span class="cw-num" x-text="cellNumbers[r+','+c] || ''"></span>
                    <input
                      :data-r="r" :data-c="c"
                      maxlength="1"
                      autocomplete="off" autocorrect="off" autocapitalize="characters" spellcheck="false"
                      :value="grid[r][c] || ''"
                      class="cw-cell"
                      :class="{
                        'active-cursor' : isCursorCell(r,c),
                        'active-word'   : isActiveCell(r,c) && !isCursorCell(r,c),
                        'cell-correct'  : submitted && cellResult[r+','+c] === 'ok',
                        'cell-incorrect': submitted && cellResult[r+','+c] === 'bad',
                        'bayes-warn'    : !submitted && bayesWarn[r+','+c],
                        'hint-letter'   : hintCells[r+','+c]
                      }"
                      :disabled="!solution[r] || !solution[r][c]"
                      :readonly="hintCells[r+','+c]"
                      @input="onCell(r,c,$event)"
                      @click="onCellClick(r,c,$event)"
                      @keydown="onKeydown(r,c,$event)"
                    >
                  </div>
                </template>
              </template>
            </div>
          </div>

          {{-- Active clue strip below grid --}}
          <div class="cw-active-clue-strip" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px" x-show="activeClues.across || activeClues.down">
            <template x-if="activeClues.across">
              <div style="flex:1;min-width:180px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:10px 14px">
                <p style="margin:0 0 3px;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#3b82f6">➡ Across</p>
                <p style="margin:0;font-size:0.82rem;color:#1e3a5f;line-height:1.5" x-text="definitions[activeClues.across]"></p>
              </div>
            </template>
            <template x-if="activeClues.down">
              <div style="flex:1;min-width:180px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:10px 14px">
                <p style="margin:0 0 3px;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#22c55e">⬇ Down</p>
                <p style="margin:0;font-size:0.82rem;color:#14532d;line-height:1.5" x-text="definitions[activeClues.down]"></p>
              </div>
            </template>
          </div>
        </div>{{-- end grid panel --}}

        {{-- RIGHT: Clue list --}}
        <div class="cw-sidebar cw-card p-4" style="position:sticky;top:72px">
          <p class="field-label" style="margin-bottom:12px">All Clues</p>

          {{-- Across --}}
          <div style="margin-bottom:14px">
            <p style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin:0 0 6px;padding-left:4px">➡ Across</p>
            <div class="clue-scroll">
              <template x-for="clue in acrossClues" :key="clue.word">
                <div class="clue-item"
                     :class="{ 'clue-across-active': activeClues.across === clue.word }"
                     @click="jumpToWord(clue.word, 'across')">
                  <strong x-text="clue.num + '.'"></strong>
                  <span x-text="' ' + clue.def"></span>
                </div>
              </template>
            </div>
          </div>

          {{-- Down --}}
          <div>
            <p style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#22c55e;margin:0 0 6px;padding-left:4px">⬇ Down</p>
            <div class="clue-scroll">
              <template x-for="clue in downClues" :key="clue.word">
                <div class="clue-item"
                     :class="{ 'clue-down-active': activeClues.down === clue.word }"
                     @click="jumpToWord(clue.word, 'down')">
                  <strong x-text="clue.num + '.'"></strong>
                  <span x-text="' ' + clue.def"></span>
                </div>
              </template>
            </div>
          </div>
        </div>{{-- end clue panel --}}

      </div>{{-- end main layout --}}

      {{-- Mobile clue list: visible only below 640px --}}
      <div class="cw-mobile-clues" x-show="grid.length" style="margin-top:14px">
        <div class="cw-card p-4">
          <p class="field-label" style="margin-bottom:10px">All Clues</p>
          <div style="margin-bottom:14px">
            <p style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin:0 0 6px;padding-left:4px">&#10132; Across</p>
            <template x-for="clue in acrossClues" :key="clue.word">
              <div class="clue-item"
                   :class="{ 'clue-across-active': activeClues.across === clue.word }"
                   @click="jumpToWord(clue.word, 'across')">
                <strong x-text="clue.num + '.'"></strong>
                <span x-text="' ' + clue.def"></span>
              </div>
            </template>
          </div>
          <div>
            <p style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#22c55e;margin:0 0 6px;padding-left:4px">&#8595; Down</p>
            <template x-for="clue in downClues" :key="clue.word">
              <div class="clue-item"
                   :class="{ 'clue-down-active': activeClues.down === clue.word }"
                   @click="jumpToWord(clue.word, 'down')">
                <strong x-text="clue.num + '.'"></strong>
                <span x-text="' ' + clue.def"></span>
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>

  </div>{{-- container --}}
</div>{{-- page --}}

{{-- ════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════ --}}
<script>
function crossword() {
  return {
    /* state */
    theme: '{{ $themes[0]->slug ?? "animals" }}',
    level: 'beginner',
    grid: [], solution: [], size: 0,
    definitions: {}, positions: {}, coordMap: {}, cellNumbers: {},
    acrossClues: [], downClues: [],
    score: 0, scorePop: false,
    loading: false, error: '', submitted: false,
    resultMsg: '', resultPerfect: false,
    activeClues: {}, currentCell: null, lastDirection: 'across',
    cellResult: {},
    /* timer */
    t0: null, timerInterval: null, timerDisplay: '00:00',

    /* ══ Feature 1: Scattered hint letters ══
       hintCells: { "r,c": true } — cells pre-filled as hints (read-only, violet).
       Beginner gets ~30% of letters as hints.
       Expert gets none. */
    hintCells: {},

    /* ══ Feature 2: RL Adaptive Difficulty ══ */
    expertUnlocked: false,   /* true when RL avg ≥ 75 on beginner */
    perfHistory: JSON.parse(localStorage.getItem('cw_perf') || '[]'),
    diffRecommendation: null,

    /* ══ AI Feature 3: Naive Bayes Typo Detection ══ */
    bayesWarn: {},       /* "r,c" -> true if suspicious letter */
    /* English bigram frequency table — P(second | first).
       Built from corpus statistics. Only storing top plausible
       successors per letter; anything not in list is low-probability. */
    BIGRAMS: {
      A:['B','C','D','F','G','H','I','L','M','N','P','R','S','T','U','V','W','X','Y'],
      B:['A','E','I','L','O','R','U','Y'],
      C:['A','E','H','I','K','L','O','R','T','U'],
      D:['A','E','I','O','R','U','Y'],
      E:['A','C','D','E','F','G','L','M','N','P','R','S','T','V','W','X','Y'],
      F:['A','E','F','I','L','O','R','T','U'],
      G:['A','E','H','I','N','O','R','U'],
      H:['A','E','I','O','U','Y'],
      I:['A','C','D','E','F','G','L','M','N','O','P','R','S','T'],
      J:['A','E','O','U'],
      K:['E','I','N','S'],
      L:['A','D','E','F','I','L','O','S','T','U','Y'],
      M:['A','E','I','O','P','U'],
      N:['A','C','D','E','G','I','N','O','S','T'],
      O:['A','B','C','D','F','G','H','I','L','M','N','P','R','S','T','U','V','W','X'],
      P:['A','E','H','I','L','O','R','T','U'],
      Q:['U'],
      R:['A','D','E','I','N','O','R','S','T','U'],
      S:['A','C','E','H','I','K','L','M','N','O','P','Q','T','U','W'],
      T:['A','E','H','I','O','R','S','U','W'],
      U:['B','G','L','M','N','P','R','S','T'],
      V:['A','E','I','O'],
      W:['A','E','H','I','N','O','R'],
      X:['A','I','P','T'],
      Y:['A','E','I','O'],
      Z:['A','E','I','O'],
    },

    /* ── timer ── */
    startTimer() {
      this.t0 = Date.now();
      clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        const s = Math.floor((Date.now() - this.t0) / 1000);
        this.timerDisplay = String(Math.floor(s/60)).padStart(2,'0') + ':' + String(s%60).padStart(2,'0');
      }, 1000);
    },
    stopTimer() { clearInterval(this.timerInterval); },

    /* ── progress ── */
    get filledCount() {
      let n = 0;
      for (let r = 0; r < this.grid.length; r++)
        for (let c = 0; c < (this.grid[r]||[]).length; c++)
          if (this.solution[r]&&this.solution[r][c]&&this.grid[r][c]) n++;
      return n;
    },
    get totalCells() {
      let n = 0;
      for (let r = 0; r < (this.solution||[]).length; r++)
        for (let c = 0; c < (this.solution[r]||[]).length; c++)
          if (this.solution[r][c]) n++;
      return n;
    },
    get progressPct() {
      return this.totalCells ? Math.round(this.filledCount/this.totalCells*100) : 0;
    },

    /* ── generate ── */
    generate() {
      this.error=''; this.loading=true; this.submitted=false;
      this.resultMsg=''; this.cellResult={};
      this.grid=[]; this.solution=[];
      this.positions={}; this.coordMap={}; this.cellNumbers={};
      this.acrossClues=[]; this.downClues=[];
      this.activeClues={}; this.currentCell=null;
      this.score=0; this.timerDisplay='00:00'; this.stopTimer();
      /* reset state */
      this.diffRecommendation=null; this.bayesWarn={};
      this.hintCells={};

      fetch('{{ route('crossword.generate') }}', {
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
        body: JSON.stringify({theme:this.theme, level:this.level})
      })
      .then(r=>r.json())
      .then(j=>{
        if(j.error){ this.error=j.error; return; }
        this.size       = j.size;
        /* Server (CrosswordBuilder) already:
           1. calls array_reverse($grid) — row 0 = top visually
           2. remaps all position rows so down-words increase downward
           Use j.grid and j.positions exactly as received. */
        this.solution   = j.grid;
        this.definitions= j.definitions||{};
        this.positions  = j.positions||{};
        this.grid       = this.solution.map(row=>row.map(cell=>cell?'':null));
        this.buildCoordMap();
        this.buildCellNumbers();
        this.buildClueLists();
        this.buildHintCells();   /* scatter pre-filled hint letters */
        this.startTimer();
      })
      .catch(()=>{ this.error='Network error — please try again.'; })
      .finally(()=>{ this.loading=false; });
    },

    /* ══ Feature 1: Build hint cells ══
       Algorithm:
       1. Collect all playable cells into an array
       2. Shuffle using Fisher-Yates
       3. Pick ~30% for Beginner (0% for Expert)
       4. Pre-fill grid with the solution letter and mark hintCells
    */
    buildHintCells() {
      this.hintCells = {};
      /* Beginner: 30% hints, Expert: 15% hints */
      const hintRatio = this.level === 'beginner' ? 0.30 : 0.15;

      /* Collect all playable cell coordinates */
      const cells = [];
      for (let r = 0; r < this.size; r++)
        for (let c = 0; c < this.size; c++)
          if (this.solution[r] && this.solution[r][c])
            cells.push({r, c});

      /* Fisher-Yates shuffle */
      for (let i = cells.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [cells[i], cells[j]] = [cells[j], cells[i]];
      }

      /* Pick 30% as hints — but ensure NOT the first cell of any word
         (keeps the puzzle challenging) */
      const firstCells = new Set();
      for (const w in this.positions) {
        const p = this.positions[w];
        const fr = p.direction === 'down' ? p.row + p.length - 1 : p.row;
        firstCells.add(`${fr},${p.col}`);
      }

      const hintCount = Math.floor(cells.length * hintRatio);
      let picked = 0;
      for (const cell of cells) {
        if (picked >= hintCount) break;
        const key = `${cell.r},${cell.c}`;
        if (firstCells.has(key)) continue;  /* skip first cells */
        /* Pre-fill the grid with the solution letter */
        const letter = this.solution[cell.r][cell.c];
        const newRow = [...this.grid[cell.r]];
        newRow[cell.c] = letter;
        this.grid[cell.r] = newRow;
        this.hintCells[key] = true;
        picked++;
      }
    },

    /* ── coord map ── */
    buildCoordMap() {
      this.coordMap={};
      for(const w in this.positions){
        const p=this.positions[w];
        for(let i=0;i<p.length;i++){
          let key;
          if(p.direction==='across'){
            key=`${p.row},${p.col+i}`;
          } else {
            /* DOWN: first letter at (p.row+p.length-1), each step r-1 */
            const r=(p.row+p.length-1)-i;
            key=`${r},${p.col}`;
          }
          if(!this.coordMap[key]) this.coordMap[key]={};
          this.coordMap[key][p.direction]=w;
        }
      }
    },

    /* ── cell numbers ── */
    buildCellNumbers() {
      this.cellNumbers={};
      const starts=[];
      for(const w in this.positions){
        const p=this.positions[w];
        /* For DOWN words, p.row is the BOTTOM cell (last letter).
           The FIRST (top) letter is at row = p.row + p.length - 1.
           For ACROSS words, p.row is already the correct first cell row. */
        const firstRow = p.direction==='down' ? p.row + p.length - 1 : p.row;
        starts.push({r:firstRow, c:p.col, word:w, dir:p.direction});
      }
      /* Sort top-to-bottom (high row = bottom, so top = low row... 
         BUT with ArrowDown=r-1, row 0 is TOP of screen.
         Top of screen = smallest row index. */
      starts.sort((a,b)=> a.r!==b.r ? a.r-b.r : a.c-b.c);
      let num=1;
      const seen={};
      for(const s of starts){
        const k=`${s.r},${s.c}`;
        if(!seen[k]) seen[k]=num++;
        this.cellNumbers[k]=seen[k];
      }
      for(const w in this.positions){
        const p=this.positions[w];
        const firstRow = p.direction==='down' ? p.row + p.length - 1 : p.row;
        p.number=seen[`${firstRow},${p.col}`]||0;
        p.firstRow=firstRow; /* store for isActiveCell & jumpToWord */
      }
    },

    /* ── clue lists ── */
    buildClueLists(){
      const ac=[],dn=[];
      for(const w in this.positions){
        const p=this.positions[w];
        const e={word:w, def:this.definitions[w]||w, num:p.number||0};
        if(p.direction==='across') ac.push(e); else dn.push(e);
      }
      this.acrossClues=ac.sort((a,b)=>a.num-b.num);
      this.downClues  =dn.sort((a,b)=>a.num-b.num);
    },

    /* ── cell state helpers ── */
    /* guard: returns true only for white (playable) cells */
    isPlayable(r,c){ return !!(this.solution[r]&&this.solution[r][c]); },
    isCursorCell(r,c){
      return this.isPlayable(r,c)&&!!this.currentCell&&this.currentCell.r===r&&this.currentCell.c===c;
    },
    isActiveCell(r,c){
      if(!this.isPlayable(r,c)) return false;          /* never highlight black squares */
      if(!this.currentCell?.word) return false;
      const pos=this.positions[this.currentCell.word];
      if(!pos) return false;
      if(pos.direction==='across'){
        return r===pos.row && c>=pos.col && c<pos.col+pos.length;
      } else {
        /* DOWN: cells run from firstRow (top) down to p.row (bottom, r decreasing) */
        const firstRow=pos.row+pos.length-1;
        return c===pos.col && r<=firstRow && r>=pos.row;
      }
    },

    /* ── click ── */
    onCellClick(r,c){
      const coord=this.coordMap[`${r},${c}`]||{};
      if(!coord.across && !coord.down) return; /* black square — ignore */

      if(this.currentCell&&this.currentCell.r===r&&this.currentCell.c===c){
        /* same cell clicked again — toggle direction if intersection */
        if(coord.across&&coord.down){
          const nd=this.currentCell.direction==='across'?'down':'across';
          this.currentCell={r,c,direction:nd,word:coord[nd]};
          this.lastDirection=nd;
        }
      } else {
        /* new cell — prefer lastDirection if available at this cell, else other direction */
        let dir=this.lastDirection;
        if(!coord[dir]) dir=(coord.across?'across':'down');
        this.currentCell={r,c,direction:dir,word:coord[dir]||null};
        this.lastDirection=dir;
      }
      this.activeClues={across:coord.across||null, down:coord.down||null};
      this.scrollActiveClue();
    },

    /* ── input ── */
    onCell(r,c,e){
      /* Hint cells are read-only — reject any change */
      if(this.hintCells[`${r},${c}`]){
        e.target.value=(this.solution[r]&&this.solution[r][c])||'';
        return;
      }
      const val=(e.target.value||'').toUpperCase().replace(/[^A-Z]/g,'').slice(0,1);
      e.target.value=val;
      /* immutably update row so Alpine detects change */
      const newRow=[...this.grid[r]]; newRow[c]=val; this.grid[r]=newRow;

      /* ══ AI Feature 3: Naive Bayes Typo Detection ══
         Check P(val | prevLetter) against the bigram table.
         If prevLetter is known and val is not a plausible successor → warn. */
      if(val){
        const key=`${r},${c}`;
        const prevLetter=this.getPrevLetter(r,c);
        if(prevLetter && this.BIGRAMS[prevLetter]){
          const plausible=this.BIGRAMS[prevLetter];
          const isWarn=!plausible.includes(val);
          const newWarn={...this.bayesWarn};
          if(isWarn) newWarn[key]=true;
          else delete newWarn[key];
          this.bayesWarn=newWarn;
        } else {
          /* no prev letter context — remove any warning */
          const newWarn={...this.bayesWarn};
          delete newWarn[key];
          this.bayesWarn=newWarn;
        }
      } else {
        /* cell cleared — remove warning */
        const newWarn={...this.bayesWarn};
        delete newWarn[`${r},${c}`];
        this.bayesWarn=newWarn;
      }

      if(val){
        const coord=this.coordMap[`${r},${c}`]||{};
        let dir=this.lastDirection;
        if(!coord[dir]) dir=(coord.across?'across':(coord.down?'down':null));
        if(dir){
          const next=this.nextCell(r,c,dir);
          if(next) setTimeout(()=>this.focusCell(next.r,next.c),0);
        }
      }
    },

    /* ── Bayes helper: get previous letter in current direction ── */
    getPrevLetter(r,c){
      const dir=this.lastDirection;
      let pr=r,pc=c;
      if(dir==='across') pc=c-1; else pr=r+1;
      if(pr<0||pr>=this.size||pc<0||pc>=this.size) return null;
      return (this.grid[pr]&&this.grid[pr][pc])||null;
    },

    /* ── keyboard ── */
    onKeydown(r,c,e){
      /* Backspace */
      if(e.key==='Backspace'){
        e.preventDefault();
        /* Hint cell — cannot delete, just move back */
        if(this.hintCells[`${r},${c}`]){
          const prev=this.prevCell(r,c,this.lastDirection);
          if(prev) this.focusCell(prev.r,prev.c);
          return;
        }
        this.backspaceCount=(this.backspaceCount||0)+1; /* RL signal tracker */
        const cur=this.grid[r]?this.grid[r][c]:'';
        if(cur){
          const nr=[...this.grid[r]]; nr[c]=''; this.grid[r]=nr;
          const inp=document.querySelector(`[data-r="${r}"][data-c="${c}"]`);
          if(inp){ inp.value=''; }
        } else {
          const prev=this.prevCell(r,c,this.lastDirection);
          if(prev) this.focusCell(prev.r,prev.c);
        }
        return;
      }
      /* Delete */
      if(e.key==='Delete'){
        e.preventDefault();
        /* Hint cell — cannot delete */
        if(this.hintCells[`${r},${c}`]) return;
        const nr=[...this.grid[r]]; nr[c]=''; this.grid[r]=nr;
        const inp=document.querySelector(`[data-r="${r}"][data-c="${c}"]`);
        if(inp) inp.value='';
        return;
      }
      /*
       * Arrow keys:
       * ArrowUp   → r + 1
       * ArrowDown → r - 1
       */
      const arrows={
        ArrowRight:[0, 1,'across'],
        ArrowLeft: [0,-1,'across'],
        ArrowUp:   [ 1,0,'down'],    /* r + 1 */
        ArrowDown: [-1,0,'down'],    /* r - 1 */
      };
      if(arrows[e.key]){
        e.preventDefault();
        const [dr,dc,dir]=arrows[e.key];
        /* walk until we find a playable cell or hit the edge */
        let nr=r+dr, nc=c+dc;
        while(nr>=0&&nr<this.size&&nc>=0&&nc<this.size){
          if(this.solution[nr]&&this.solution[nr][nc]){
            this.lastDirection=dir;
            this.focusCell(nr,nc);
            break;
          }
          nr+=dr; nc+=dc;
        }
      }
    },

    /* ── cell movement helpers ── */
    nextCell(r,c,dir){
      if(dir==='across'){
        if(c+1<this.size&&this.solution[r]&&this.solution[r][c+1]) return {r,c:c+1};
      } else {
        /* down = r - 1 (matches ArrowDown) */
        if(r-1>=0&&this.solution[r-1]&&this.solution[r-1][c]) return {r:r-1,c};
      }
      return null;
    },
    prevCell(r,c,dir){
      if(dir==='across'){
        if(c-1>=0&&this.solution[r]&&this.solution[r][c-1]) return {r,c:c-1};
      } else {
        /* up = r + 1 (matches ArrowUp) */
        if(r+1<this.size&&this.solution[r+1]&&this.solution[r+1][c]) return {r:r+1,c};
      }
      return null;
    },

    focusCell(r,c){
      const inp=document.querySelector(`[data-r="${r}"][data-c="${c}"]`);
      if(inp&&!inp.disabled){
        /* sync DOM value in case Alpine reactive re-render lagged */
        const stored=(this.grid[r]&&this.grid[r][c])||'';
        if(inp.value!==stored) inp.value=stored;
        inp.focus();
        /* select all text so next keypress replaces cleanly */
        inp.select();
        const coord=this.coordMap[`${r},${c}`]||{};
        const dir=coord[this.lastDirection]?this.lastDirection:(coord.across?'across':(coord.down?'down':this.lastDirection));
        const word=coord[dir]||null;
        this.currentCell={r,c,direction:dir,word};
        this.lastDirection=dir;
        this.activeClues={across:coord.across||null, down:coord.down||null};
        this.scrollActiveClue();
      }
    },

    /* ── jump from clue list ── */
    jumpToWord(word,dir){
      const pos=this.positions[word]; if(!pos) return;
      this.lastDirection=dir;
      /* For DOWN words, jump to the first (top) letter */
      const row = dir==='down' ? pos.row+pos.length-1 : pos.row;
      this.focusCell(row,pos.col);
    },

    scrollActiveClue(){
      this.$nextTick(()=>{
        const el=document.querySelector('.clue-across-active,.clue-down-active');
        if(el) el.scrollIntoView({block:'nearest',behavior:'smooth'});
      });
    },

    /* ── clear ── */
    clearGrid(){
      /* Reset grid but keep hint letters in place */
      this.grid = this.solution.map((row, r) =>
        row.map((cell, c) => {
          if (!cell) return null;                          /* black square */
          if (this.hintCells[`${r},${c}`]) return cell;   /* keep hint letter */
          return '';                                        /* clear user input */
        })
      );
      this.submitted=false; this.cellResult={}; this.resultMsg='';
      this.activeClues={}; this.currentCell=null;
      this.bayesWarn={}; this.backspaceCount=0;
      this.diffRecommendation=null;
      this.$nextTick(()=>{
        document.querySelectorAll('.cw-cell:not(:disabled)').forEach(inp=>{
          const r=parseInt(inp.dataset.r), c=parseInt(inp.dataset.c);
          /* restore hint value in DOM, clear others */
          inp.value = this.hintCells[`${r},${c}`]
            ? (this.solution[r]&&this.solution[r][c])||''
            : '';
        });
      });
    },


    /* ── submit ── */
    submitPuzzle(){
      if(!this.grid.length) return;
      this.stopTimer();
      const dur=Math.round((Date.now()-this.t0)/1000);

      /* Grid is already in server row order (row 0 = top, matching session solution). */
      /* Session solution = already-flipped grid (row 0 = top).
         Our this.grid uses the same orientation — send as-is. */
      fetch('{{ route('crossword.submit') }}',{
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
        body:JSON.stringify({grid:this.grid, duration_sec:dur})
      })
      .then(r=>r.json())
      .then(j=>{
        this.submitted=true;
        /* per-cell colouring */
        const res={};
        for(const w in this.positions){
          const pos=this.positions[w];
          for(let i=0;i<pos.length;i++){
            const cr=pos.direction==='across'?pos.row:pos.row+i;
            const cc=pos.direction==='across'?pos.col+i:pos.col;
            const entered=(this.grid[cr]&&this.grid[cr][cc])?this.grid[cr][cc].toUpperCase():'';
            const correct=(this.solution[cr]&&this.solution[cr][cc])?this.solution[cr][cc].toUpperCase():'';
            const key=`${cr},${cc}`;
            /* only mark playable (white) cells */
            if(!res[key]&&correct) res[key]=(entered&&entered===correct)?'ok':'bad';
          }
        }
        this.cellResult=res;
        /* score animate */
        const prev=this.score; this.score=j.score;
        if(j.score!==prev){
          this.scorePop=false;
          this.$nextTick(()=>{ this.scorePop=true; setTimeout(()=>{ this.scorePop=false; },500); });
        }
        this.resultPerfect=(j.correct===j.total);
        this.resultMsg=`${j.correct}/${j.total} correct · ${j.score} pts · Time: ${this.timerDisplay}`;

        /* ══ AI Feature 2: Adaptive Difficulty — RL-style signal ══
           Scoring rules (0-100):
           - accuracy   (0-60 pts): correct/total × 60  ← dominant signal
           - speed      (0-25 pts): only meaningful when accuracy ≥ 50%
                        speed is multiplied by accuracy so fast+wrong = 0
           - backspaces (0-15 pts): multiplied by accuracy so it can't
                        compensate for low correctness
           Gate: if accuracy < 30%, total score is capped at 20
                 regardless of speed or backspaces. */
        const accuracy   = j.total > 0 ? (j.correct / j.total) : 0;
        const speedSecs  = dur;

        /* Speed score: full points for ≤90s, zero for ≥360s.
           Then SCALED by accuracy — fast+wrong earns nothing. */
        const rawSpeed   = Math.max(0, Math.min(25, 25 - ((speedSecs - 90) / 270) * 25));
        const speedScore = rawSpeed * accuracy;          /* 0 if accuracy = 0 */

        /* Backspace score: also scaled by accuracy */
        const backspaces = this.backspaceCount || 0;
        const rawBS      = Math.max(0, 15 - Math.min(15, backspaces * 0.75));
        const bsScore    = rawBS * accuracy;             /* 0 if accuracy = 0 */

        /* Raw total */
        let perfScore = Math.round(accuracy * 60 + speedScore + bsScore);

        /* Hard gate: accuracy < 30% → cap at 20 */
        if (accuracy < 0.30) perfScore = Math.min(perfScore, 20);

        /* Store in history (max 10 sessions) */
        this.perfHistory.push({ score: perfScore, level: this.level, ts: Date.now() });
        if(this.perfHistory.length>10) this.perfHistory.shift();
        try { localStorage.setItem('cw_perf', JSON.stringify(this.perfHistory)); } catch(e){}

        /* RL reward signal: average last 3 sessions */
        const recent = this.perfHistory.slice(-3);
        const avg    = recent.reduce((s,x)=>s+x.score,0)/recent.length;

        let rec;
        if(this.level==='beginner'){
          if(avg>=75){
            rec={ label:'Naik ke Expert!', message:'Performa kamu sangat baik di Beginner (avg '+Math.round(avg)+'/100). AI merekomendasikan coba Expert.', badgeClass:'diff-hard' };
            this.expertUnlocked=true;  /* RL unlocks the Go Expert button */
          }
          else if(avg>=50) rec={ label:'Pertahankan Beginner', message:'Kamu sudah cukup konsisten. Terus latih kecepatan dan akurasi.', badgeClass:'diff-medium' };
          else rec={ label:'Beginner — terus berlatih', message:'Fokus pada akurasi dulu. Jangan terburu-buru mengisi jawaban.', badgeClass:'diff-easy' };
        } else {
          if(avg>=75) rec={ label:'Expert — sangat bagus!', message:'Performa Expert kamu luar biasa (avg '+Math.round(avg)+'/100). Kamu sudah mahir!', badgeClass:'diff-hard' };
          else if(avg>=40) rec={ label:'Tetap di Expert', message:'Progres yang bagus di Expert. Terus tingkatkan kecepatan.', badgeClass:'diff-medium' };
          else rec={ label:'Coba Beginner dulu', message:'Expert cukup sulit. AI sarankan kembali ke Beginner untuk membangun kepercayaan diri.', badgeClass:'diff-easy' };
        }
        this.diffRecommendation=rec;
        this.backspaceCount=0; /* reset for next game */
      })
      .catch(()=>{ this.error='Submit failed — please try again.'; });
    }
  };
}
</script>

</x-app-layout>