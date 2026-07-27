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

/* Cell-size token — overridden to 1.7rem on mobile */
:root { --cw-cell: 2.4rem; }

.cw-wrap { font-family: 'Poppins', 'Segoe UI', sans-serif; overflow-x: hidden; }

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
  width: var(--cw-cell); height: var(--cw-cell);
  text-align: center; line-height: var(--cw-cell);
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
/* ── RL unlock: level-up button glow ── */
@keyframes levelGlow {
  0%,100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.5); }
  50%      { box-shadow: 0 0 0 10px rgba(99,102,241,0); }
}
.btn-unlock-inter {
  background: linear-gradient(135deg,#22c55e,#0ea5e9);
  color:#fff; animation: levelGlow 1.8s ease infinite;
}
.btn-unlock-inter:hover { background: linear-gradient(135deg,#16a34a,#0284c7); }
.btn-unlock-expert {
  background: linear-gradient(135deg,#ef4444,#f97316);
  color:#fff; animation: levelGlow 1.8s ease infinite;
}
.btn-unlock-expert:hover { background: linear-gradient(135deg,#dc2626,#ea580c); }

/* ── RL: level unlock banner ── */
.unlock-banner {
  border-radius: 14px; padding: 14px 18px;
  display: flex; align-items: center; gap: 14px;
  animation: fadeSlideIn 0.4s ease;
  border: 2px solid;
}
.unlock-banner-inter { background: linear-gradient(135deg,#f0fdf4,#ecfeff); border-color: #6ee7b7; }
.unlock-banner-expert { background: linear-gradient(135deg,#fff7ed,#fef2f2); border-color: #fca5a5; }

/* ── Locked select option ── */
option:disabled { color: #94a3b8; }

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
  display: flex;
  align-items: flex-start;
  gap: 4px;
  padding: 0.45rem 0.6rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.8rem;
  line-height: 1.45;
  color: #334155;
  transition: background 0.1s;
  border-left: 3px solid transparent;
}
.wiki-img-btn {
  flex-shrink: 0;
  margin-left: auto;
  align-self: center;
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 2px 6px;
  font-size: 0.7rem;
  cursor: pointer;
  color: #64748b;
  transition: background 0.12s, border-color 0.12s, color 0.12s;
  line-height: 1.5;
  white-space: nowrap;
}
.wiki-img-btn:hover { background: #eff6ff; border-color: #6366f1; color: #6366f1; }
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
  /* Reduce base cell token; JS auto-fit will scale the whole grid to fit viewport */
  :root { --cw-cell: 1.25rem; }
  .cw-cell { font-size: 0.75rem !important; border-radius: 4px !important; }
  #cw-grid > div { width: var(--cw-cell) !important; height: var(--cw-cell) !important; }
  #cw-grid { gap: 2px !important; padding: 2px !important; }
  /* Remove fixed min-height so the viewport shrinks to match the scaled grid */
  #cw-panzoom-viewport { min-height: unset !important; padding: 12px 0 !important; }

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

/* ── AI: Bayes warning cell ── (disabled — Naive Bayes not active)
.cw-cell:not(:disabled).bayes-warn {
  box-shadow: 0 0 0 2px #f59e0b, inset 0 0 0 1px #fef3c7;
  background: #fffbeb; color: #78350f;
} */

/* ── Pan-zoom grid viewport ── */
#cw-panzoom-viewport {
  overflow: hidden;
  width: 100%;
  box-sizing: border-box;
  padding-bottom: 4px;
  background: transparent;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  position: relative;
  -webkit-user-select: none;
  user-select: none;
}
#cw-panzoom-inner {
  transform-origin: center center;
  will-change: transform;
  display: inline-flex;
  flex-shrink: 0;       /* keep natural width so computeFitScale() measures correctly */
  touch-action: none;
}
#cw-zoom-reset {
  display: none;
  position: absolute;
  top: 8px; right: 8px;
  z-index: 20;
  background: rgba(15,23,42,0.62);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 5px 11px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  letter-spacing: .02em;
  backdrop-filter: blur(4px);
}
@media (min-width: 641px) {
  /* Desktop: restore horizontal scroll if grid ever overflows */
  #cw-panzoom-viewport { overflow-x: auto; }
}
</style>

{{-- ════════════════════════════════════════
     PAGE
════════════════════════════════════════ --}}
<div x-data="crossword()" class="cw-wrap min-h-[calc(100vh-4rem)] bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50">
  <div class="max-w-7xl mx-auto p-6">

    {{-- ── Page title row ── --}}
    <div class="cw-header-row flex flex-wrap items-center gap-3 mt-4 mb-6 ">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center">
          <span style="font-size:1.15rem">🧩</span>
        </div>
        <div>
          <h1 style="margin:0;font-size:1.2rem;font-weight:800;color:#1e293b;line-height:1.2">Crossword</h1>
          <p style="margin:0;font-size:0.7rem;color:#94a3b8;font-weight:500">Pick a theme, generate, and solve!</p>
        </div>
      </div>

      <div x-show="grid.length" style="margin-left:auto;display:flex;align-items:center;gap:10px">
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
    <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-4 mb-6">
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
          <select x-model="level" @change="onLevelChange()" class="cw-select" style="min-width:195px">
            <option value="beginner">🌱 Beginner — 12×12</option>
            <option value="intermediate"
                    :disabled="!unlockedLevels.intermediate"
                    x-text="unlockedLevels.intermediate ? '🌿 Intermediate — 13×13' : '🔒 Intermediate (terkunci)'">
            </option>
            <option value="expert"
                    :disabled="!unlockedLevels.expert"
                    x-text="unlockedLevels.expert ? '🔥 Expert & Beyond — 15×15' : '🔒 Expert & Beyond (terkunci)'">
            </option>
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
<a x-show="submitted"
             :href="'{{ route('leaderboard.index') }}?game=crossword&level=' + level"
             class="btn-primary"
             style="background:#faf5ff;color:#7c3aed;border:1.5px solid #c4b5fd;text-decoration:none">
            🏆 Leaderboard
          </a>
          {{-- RL unlock: jump to newly unlocked level --}}
          <button x-show="newLevelUnlocked && level !== newLevelUnlocked"
                  @click="level = newLevelUnlocked; newLevelUnlocked = null; generate();"
                  class="btn-primary"
                  :class="newLevelUnlocked === 'expert' ? 'btn-unlock-expert' : 'btn-unlock-inter'"
                  :title="'AI: you are ready to advance to ' + (newLevelUnlocked === 'intermediate' ? 'Intermediate' : 'Expert & Beyond') + '!'">
            <span x-text="newLevelUnlocked === 'intermediate' ? '🌿 Go Intermediate!' : '🔥 Go Expert & Beyond!'"></span>
          </button>
        </div>
      </div>
    </div>

    {{-- Error --}}
    <template x-if="error">
      <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:12px 16px;color:#991b1b;font-size:0.85rem;margin-bottom:16px" class="fade-in" x-text="error"></div>
    </template>

    {{-- ══ Unified Result Card (Result + GBA + Recommendation) ══ --}}
    <template x-if="resultMsg">
      <div class="cw-card fade-in" style="padding:20px;margin-bottom:16px">

        {{-- Title row --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
          <span style="font-size:1.6rem" x-text="resultPerfect ? '🏆' : '📝'"></span>
          <div>
            <p style="margin:0;font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8">Hasil Puzzle</p>
            <p style="margin:0;font-size:0.92rem;font-weight:700;color:#1e293b"
               x-text="resultPerfect ? 'Perfect! All words correct.' : 'Puzzle done. See details below.'"></p>
          </div>
        </div>

        {{-- Stats: Score · Words Correct · Time --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:12px;background:#f8fafc;border-radius:10px;margin-bottom:14px">
          <div style="text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:#6366f1" x-text="score"></div>
            <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-top:2px">Score / 100</div>
          </div>
          <div style="text-align:center;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
            <div style="font-size:1.5rem;font-weight:800;color:#22c55e" x-text="resultWordsCorrect + '/' + resultTotalWords"></div>
            <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-top:2px">Words Correct</div>
          </div>
          <div style="text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:#f59e0b" x-text="timerDisplay"></div>
            <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-top:2px">Time</div>
          </div>
        </div>

        {{-- GBA Assessment --}}
        <div x-show="showGbaCard" style="border-top:1px solid #e2e8f0;padding-top:12px;margin-bottom:12px">
          <p style="margin:0 0 8px;font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#6366f1">📊 Game-Based Assessment</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <div style="text-align:center;background:#eef2ff;border-radius:8px;padding:10px">
              <div style="font-size:1.25rem;font-weight:800;color:#4f46e5" x-text="(gbaTheta * 100).toFixed(1) + '%'"></div>
              <div style="font-size:0.65rem;color:#818cf8;margin-top:2px">Your Ability (θ, baseline 0.30)</div>
            </div>
            <div style="text-align:center;background:#f5f3ff;border-radius:8px;padding:10px">
              <div style="font-size:1.25rem;font-weight:800;color:#7c3aed" x-text="(gbaLdNext * 100).toFixed(1) + '%'"></div>
              <div style="font-size:0.65rem;color:#a78bfa;margin-top:2px">Next Difficulty</div>
            </div>
          </div>
        </div>

        {{-- Adaptive Difficulty Recommendation (inline) --}}
        <div x-show="diffRecommendation" style="display:flex;align-items:flex-start;gap:8px;background:#f8fafc;border-radius:8px;padding:10px">
          <span style="font-size:0.9rem;margin-top:1px">🧠</span>
          <div style="flex:1">
            <span class="diff-badge" :class="diffRecommendation?.badgeClass"
                  x-text="diffRecommendation?.label"
                  style="margin-bottom:5px;display:inline-block"></span>
            <p style="margin:0;font-size:0.78rem;color:#475569;line-height:1.5" x-text="diffRecommendation?.message"></p>
          </div>
        </div>

      </div>
    </template>

    {{-- RL: Level unlock banner --}}
    <template x-if="newLevelUnlocked">
      <div class="unlock-banner mb-4"
           :class="newLevelUnlocked === 'expert' ? 'unlock-banner-expert' : 'unlock-banner-inter'">
        <span style="font-size:1.8rem" x-text="newLevelUnlocked === 'intermediate' ? '🌿' : '🔥'"></span>
        <div style="flex:1">
          <p style="margin:0 0 3px;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#15803d"
             x-show="newLevelUnlocked === 'intermediate'">New Level Unlocked!</p>
          <p style="margin:0 0 3px;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#b91c1c"
             x-show="newLevelUnlocked === 'expert'">New Level Unlocked!</p>
          <p style="margin:0;font-size:0.88rem;font-weight:600;color:#1e293b"
             x-text="newLevelUnlocked === 'intermediate'
               ? 'Great job! Your performance is consistent. Intermediate level is now available!'
               : 'Amazing! You have mastered Intermediate. Expert & Beyond level is now available!'"></p>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
          <button @click="level = newLevelUnlocked; newLevelUnlocked = null; generate();"
                  class="btn-primary"
                  :class="newLevelUnlocked === 'expert' ? 'btn-unlock-expert' : 'btn-unlock-inter'">
            Play Now
          </button>
          <button @click="newLevelUnlocked = null" class="btn-primary btn-ghost" style="padding:0.45rem 0.9rem">
            Nanti
          </button>
        </div>
      </div>
    </template>

    {{-- AI Feature 2: Adaptive Difficulty Recommendation (sudah digabung ke Result Card) --}}
    <template x-if="false && diffRecommendation">
      <div style="display:none">
        <span x-text="diffRecommendation.label"></span>
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
      <div class="cw-main-layout fade-in grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-4 lg:gap-[18px] items-start">

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

          {{-- Grid: pan-and-zoom on mobile touch, horizontal-scroll on desktop --}}
          <div id="cw-panzoom-viewport">
            <button id="cw-zoom-reset" onclick="cwResetZoom()">⊖ Reset Zoom</button>
            <div id="cw-panzoom-inner">
              <div id="cw-grid" :style="`grid-template-columns: repeat(${size}, var(--cw-cell));`">
                <template x-for="(row, r) in grid" :key="'r'+r">
                  <template x-for="(cell, c) in row" :key="'c'+c">
                    <div style="position:relative;width:var(--cw-cell);height:var(--cw-cell)">
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
                          /* 'bayes-warn': !submitted && bayesWarn[r+','+c], */
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
            </div>{{-- /cw-panzoom-inner --}}
          </div>{{-- /cw-panzoom-viewport --}}

          {{-- Active clue strip below grid --}}
          <div class="cw-active-clue-strip" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px" x-show="activeClues.across || activeClues.down">
            <template x-if="activeClues.across">
              <div style="flex:1;min-width:180px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:10px 14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px">
                  <p style="margin:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#3b82f6">➡ Across</p>
                  <button @click="fetchWikiImage(activeClues.across, definitions[activeClues.across])" class="wiki-img-btn" title="See Wikipedia image" style="border-color:#bfdbfe">🖼 Image</button>
                </div>
                <p style="margin:0;font-size:0.82rem;color:#1e3a5f;line-height:1.5" x-text="definitions[activeClues.across]"></p>
              </div>
            </template>
            <template x-if="activeClues.down">
              <div style="flex:1;min-width:180px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:10px 14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px">
                  <p style="margin:0;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#22c55e">⬇ Down</p>
                  <button @click="fetchWikiImage(activeClues.down, definitions[activeClues.down])" class="wiki-img-btn" title="See Wikipedia image" style="border-color:#86efac">🖼 Image</button>
                </div>
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
                  <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:flex-start;gap:4px">
                      <strong x-text="clue.num + '.'"></strong>
                      <span x-text="clue.def" style="flex:1"></span>
                    </div>
                  </div>
                  <button @click.stop="fetchWikiImage(clue.word, clue.def)" class="wiki-img-btn" title="See Wikipedia image" style="flex-shrink:0">🖼</button>
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
                  <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:flex-start;gap:4px">
                      <strong x-text="clue.num + '.'"></strong>
                      <span x-text="clue.def" style="flex:1"></span>
                    </div>
                  </div>
                  <button @click.stop="fetchWikiImage(clue.word, clue.def)" class="wiki-img-btn" title="See Wikipedia image" style="flex-shrink:0">🖼</button>
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
                <span x-text="' ' + clue.def" style="flex:1"></span>
                <button @click.stop="fetchWikiImage(clue.word, clue.def)" class="wiki-img-btn" title="Wikipedia image">🖼</button>
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
                <span x-text="' ' + clue.def" style="flex:1"></span>
                <button @click.stop="fetchWikiImage(clue.word, clue.def)" class="wiki-img-btn" title="Wikipedia image">🖼</button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>

  </div>{{-- container --}}

  {{-- ── Wikipedia Image Modal ── --}}
  <div x-show="wikiModal.open"
       @keydown.escape.window="wikiModal.open=false"
       style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:1000">
    <div @click.self="wikiModal.open=false"
         style="width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:20px;box-sizing:border-box">
    <div style="background:#fff;border-radius:20px;padding:24px;max-width:420px;width:100%;box-shadow:0 25px 80px rgba(0,0,0,0.3);position:relative;max-height:88vh;overflow-y:auto">

      {{-- Close button --}}
      <button @click="wikiModal.open=false"
              style="position:absolute;top:12px;right:12px;background:#f1f5f9;border:none;border-radius:8px;width:28px;height:28px;cursor:pointer;font-size:0.85rem;color:#64748b;display:flex;align-items:center;justify-content:center;line-height:1">✕</button>

      {{-- Header --}}
      <//p style="margin:0 0 2px;font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1">📖 Wikipedia Image</p>
      <//p style="margin:0 0 4px;font-size:1rem;font-weight:800;color:#1e293b;text-transform:capitalize;padding-right:32px" x-text="wikiModal.title"></p>
      <//p style="margin:0 0 14px;font-size:0.78rem;color:#64748b;line-height:1.5" x-text="wikiModal.def" x-show="wikiModal.def"></p>

      {{-- Loading --}}
      <div x-show="wikiModal.loading" style="text-align:center;padding:40px 20px">
        <span class="spin" style="font-size:2.5rem;display:inline-block">⟳</span>
        <p style="margin:12px 0 0;font-size:0.85rem;color:#64748b">Mencari gambar di Wikipedia…</p>
      </div>

      {{-- Error --}}
      <div x-show="!wikiModal.loading && wikiModal.error" style="text-align:center;padding:30px 20px">
        <div style="font-size:2.5rem;margin-bottom:10px">🔍</div>
        <p style="font-size:0.85rem;color:#ef4444;margin:0" x-text="wikiModal.error"></p>
      </div>

      {{-- Image --}}
      <div x-show="!wikiModal.loading && wikiModal.image">
        <img :src="wikiModal.image" :alt="wikiModal.title"
             style="width:100%;border-radius:10px;display:block;margin-bottom:10px;object-fit:cover;max-height:300px"
             @@error="wikiModal.error='Gagal memuat gambar.';wikiModal.image=null">
        <p style="margin:0;font-size:0.72rem;color:#94a3b8;text-align:center">
          Sumber:&nbsp;<//a :href="'https://en.wikipedia.org/wiki/'+encodeURIComponent(wikiModal.title)"
                          target="_blank" rel="noopener"
                          style="color:#6366f1;text-decoration:underline"
                          x-text="wikiModal.title"></a>&nbsp;— Wikipedia
        </p>
      </div>

    </div>
    </div>{{-- /flex centering wrapper --}}
  </div>{{-- /x-show overlay --}}

</div>{{-- page --}}

{{-- ════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════ --}}
<script>
function crossword() {
  return {
    /* ══════════════════════════════════════════════════════════════
       RULE-BASED SYSTEM — Knowledge Base
       Semua aturan permainan Crossword didefinisikan terpusat di sini.
       Inference Engine: buildHintCells(), onCell(),
                         onKeydown(), submitPuzzle()
       ══════════════════════════════════════════════════════════════ */
    CROSSWORD_RULES: {
      // R1 — Aturan Scaffolding Bantuan (hint letters per level)
      hintRatio:    { beginner: 0.30, intermediate: 0.20, expert: 0.15 },
      hintsAllowed: { beginner: 5,    intermediate: 3,    expert: 2    },

      // R2 — Aturan Validasi Input
      allowedPattern:   /^[A-Z]$/,
      hintCellReadOnly: true,
      maxCharsPerCell:  1,

      // R3 — Aturan Navigasi Grid
      // (diimplementasikan langsung di fungsi onKeydown(),
      //  tidak memerlukan parameter konstanta tambahan)

      // R4 — Aturan Evaluasi Jawaban
      cellResultMap:    { correct: 'ok', incorrect: 'bad' },
      perfectScore:     100,

      // R5 — Aturan Unlock Level
      unlockThreshold:  { intermediate: 60, expert: 70 },
      sessionWindow:    3,
      minAccuracyGate:  0.30,
      perfCapBelowGate: 20,

    },


    /* state */
    theme: '{{ $themes[0]->slug ?? "animals" }}',
    level: 'beginner',
    grid: [], solution: [], size: 0,
    definitions: {}, positions: {}, coordMap: {}, cellNumbers: {},
    acrossClues: [], downClues: [],
    score: 0, scorePop: false,
    loading: false, error: '', submitted: false,
    resultMsg: '', resultPerfect: false, resultWordsCorrect: 0, resultTotalWords: 0,
    activeClues: {}, currentCell: null, lastDirection: 'across',
    cellResult: {},
    /* timer */
    t0: null, timerInterval: null, timerDisplay: '00:00',
    /* Wikipedia image modal */
    wikiModal: { open: false, loading: false, image: null, title: '', def: '', error: '' },

    /* GBA/DDA state */
    gbaTheta:       0,
    gbaLdNext:      0.30,
    gbaLdCurrent:   0.30,
    hintsUsed:      0,
    hintsAvailable: 3,
    showGbaCard:    false,

    /* ══ Feature 1: Scattered hint letters ══
       hintCells: { "r,c": true } — cells pre-filled as hints (read-only, violet).
       Beginner: ~30%, Intermediate: ~20%, Expert: ~15%. */
    hintCells: {},

    /* ══ Feature 2: RL Adaptive Difficulty (3-level) ══
       Levels: beginner → intermediate → expert
       Unlock thresholds (avg perf_score over last 3 same-level sessions):
         beginner  ≥ 60  → unlocks intermediate
         intermediate ≥ 70 → unlocks expert
       perf_score: accuracy*60 + speed*25 + backspace*15 (all weighted by accuracy) */
    perfHistory:    JSON.parse(localStorage.getItem('cw_perf') || '[]'),
    unlockedLevels: @json($unlockedLevels),   /* server-side, per-akun */
    newLevelUnlocked: null,   /* 'intermediate' | 'expert' | null */
    diffRecommendation: null,

    /* ══ AI Feature 3: Naive Bayes Typo Detection ══ (disabled — not needed for now) */
    bayesWarn: {},   /* warning state — kept so template references don't break */
    /* BIGRAMS: {
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
    }, */

    /* ── level change guard (prevent selecting locked level) ── */
    onLevelChange() {
      if (this.level === 'intermediate' && !this.unlockedLevels.intermediate) this.level = 'beginner';
      if (this.level === 'expert'       && !this.unlockedLevels.expert)       this.level = 'beginner';
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
      this.hintCells={}; this.hintsUsed=0; this.showGbaCard=false;

      // Fetch next-ld dari DDA hanya untuk Expert & Beyond
      if (this.level === 'expert') {
        fetch('{{ route("crossword.next-ld") }}')
          .then(r => r.json())
          .then(d => { this.gbaLdCurrent = d.ld_next ?? 0.30; })
          .catch(() => {})
          .finally(() => { this._doGenerate(); });
      } else {
        this._doGenerate();
      }
    },

    _doGenerate() {
      fetch('{{ route('crossword.generate') }}', {
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
        body: JSON.stringify({theme:this.theme, level:this.level, ld_target: this.level === 'expert' ? this.gbaLdCurrent : null})
      })
      .then(r=>r.json())
      .then(j=>{
        if(j.error){ this.error=j.error; return; }
        this.size       = j.size;
        /* Server (CrosswordBuilder) already:
           1. calls array_reverse($grid) — row 0 = top visually
           2. remaps all position rows so down-words increase downward
           Use j.grid and j.positions exactly as received. */
        this.solution      = j.grid;
        this.definitions   = j.definitions||{};
        this.positions     = j.positions||{};
        this.grid       = this.solution.map(row=>row.map(cell=>cell?'':null));
        this.buildCoordMap();
        this.buildCellNumbers();
        this.buildClueLists();
        this.buildHintCells();   /* scatter pre-filled hint letters */
        this.startTimer();
        /* After Alpine renders all cells, auto-fit the grid to the mobile viewport */
        this.$nextTick(() => { this.$nextTick(() => { window.cwFitGrid && window.cwFitGrid(); }); });
      })
      .catch(()=>{ this.error='Network error — please try again.'; })
      .finally(()=>{ this.loading=false; });
    },  // end _doGenerate

    /* ══ Feature 1: Build hint cells ══
       Algorithm:
       1. Collect all playable cells into an array
       2. Shuffle using Fisher-Yates
       3. Pick ~30% for Beginner (0% for Expert)
       4. Pre-fill grid with the solution letter and mark hintCells
    */
    buildHintCells() {
      this.hintCells = {};
      /* Beginner: 30% hints, Intermediate: 20%, Expert: 15% */
      const hintRatio = this.level === 'beginner' ? 0.30 : (this.level === 'intermediate' ? 0.20 : 0.15);

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

      /* ══ AI Feature 3: Naive Bayes Typo Detection ══ (disabled — not needed for now)
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
          const newWarn={...this.bayesWarn};
          delete newWarn[key];
          this.bayesWarn=newWarn;
        }
      } else {
        const newWarn={...this.bayesWarn};
        delete newWarn[`${r},${c}`];
        this.bayesWarn=newWarn;
      }
      */

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

    /* ── Bayes helper: get previous letter in current direction ── (disabled)
    getPrevLetter(r,c){
      const dir=this.lastDirection;
      let pr=r,pc=c;
      if(dir==='across') pc=c-1; else pr=r+1;
      if(pr<0||pr>=this.size||pc<0||pc>=this.size) return null;
      return (this.grid[pr]&&this.grid[pr][pc])||null;
    },
    */

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
        body:JSON.stringify({
          grid:this.grid,
          duration_sec:dur,
          hints_used:this.hintsUsed,
          hints_available:this.hintsAvailable,
        })
      })
      .then(r=>r.json())
      .then(j=>{
        this.submitted=true;
        /* per-cell colouring — derived client-side from grid vs solution
           DOWN coordinate: huruf pertama di row = pos.row+pos.length-1, turun ke pos.row */
        const res={};
        for(const w in this.positions){
          const pos=this.positions[w];
          for(let i=0;i<pos.length;i++){
            const cr = pos.direction==='across' ? pos.row : (pos.row + pos.length - 1 - i);
            const cc = pos.direction==='across' ? pos.col + i : pos.col;
            const entered=(this.grid[cr]&&this.grid[cr][cc])?this.grid[cr][cc].toUpperCase():'';
            const correct=(this.solution[cr]&&this.solution[cr][cc])?this.solution[cr][cc].toUpperCase():'';
            const key=`${cr},${cc}`;
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
        this.resultPerfect = (j.wordsCorrect === j.totalWords);
        this.resultWordsCorrect = j.wordsCorrect;
        this.resultTotalWords   = j.totalWords;
        this.resultMsg = `${j.wordsCorrect}/${j.totalWords} words correct · ${j.score}/100 pts · Time: ${this.timerDisplay}`;

        /* ══ RL Adaptive Difficulty (3-level) ══
           perf_score (0–100):
             accuracy*60  — word-based accuracy (dominant signal)
             speed*25     — scaled by accuracy, baseline per level
             backspace*15 — scaled by accuracy
           Gate: accuracy < 30% → cap at 20 */
        const accuracy   = j.totalWords > 0 ? (j.wordsCorrect / j.totalWords) : 0;
        const baseTime   = this.level === 'beginner' ? 120 : (this.level === 'intermediate' ? 180 : 300);
        const rawSpeed   = Math.max(0, Math.min(25, 25 - ((dur - baseTime) / (baseTime * 2)) * 25));
        const speedScore = rawSpeed * accuracy;
        const backspaces = this.backspaceCount || 0;
        const rawBS      = Math.max(0, 15 - Math.min(15, backspaces * 0.75));
        const bsScore    = rawBS * accuracy;
        let perfScore    = Math.round(accuracy * 60 + speedScore + bsScore);
        if (accuracy < 0.30) perfScore = Math.min(perfScore, 20);

        /* Append to history, cap at 20 entries */
        this.perfHistory.push({ score: perfScore, level: this.level, ts: Date.now() });
        if (this.perfHistory.length > 20) this.perfHistory.shift();
        try { localStorage.setItem('cw_perf', JSON.stringify(this.perfHistory)); } catch(e) {}

        /* RL unlock: avg of last 3 sessions AT THIS LEVEL */
        const sameLvl  = this.perfHistory.filter(h => h.level === this.level).slice(-3);
        const levelAvg = sameLvl.length > 0
          ? Math.round(sameLvl.reduce((s, x) => s + x.score, 0) / sameLvl.length)
          : 0;

        let justUnlocked = null;
        if (this.level === 'beginner' && levelAvg >= 60 && !this.unlockedLevels.intermediate) {
          this.unlockedLevels = { ...this.unlockedLevels, intermediate: true };
          justUnlocked = 'intermediate';
        } else if (this.level === 'intermediate' && levelAvg >= 70 && !this.unlockedLevels.expert) {
          this.unlockedLevels = { ...this.unlockedLevels, expert: true };
          justUnlocked = 'expert';
        }
        if (justUnlocked) {
          this.newLevelUnlocked = justUnlocked;
          /* Simpan unlock ke server (per-akun) */
          fetch('{{ route('crossword.unlock') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ level: justUnlocked })
          });
        }

        /* Recommendation message (3-level aware) */
        let rec;
        if (this.level === 'beginner') {
          if (justUnlocked === 'intermediate' || (levelAvg >= 60 && this.unlockedLevels.intermediate))
            rec = { label:'Advance to Intermediate!', message:'Great! Your Beginner avg is '+levelAvg+'/100. Intermediate level is now unlocked!', badgeClass:'diff-medium' };
          else if (levelAvg >= 40)
            rec = { label:'Keep Practicing', message:'Good consistency. Reach avg ≥ 60 to unlock Intermediate (current: '+levelAvg+'/100).', badgeClass:'diff-easy' };
          else
            rec = { label:'Focus on Accuracy', message:'Try to get more answers correct first. Target avg ≥ 60 to unlock Intermediate.', badgeClass:'diff-easy' };
        } else if (this.level === 'intermediate') {
          if (justUnlocked === 'expert' || (levelAvg >= 70 && this.unlockedLevels.expert))
            rec = { label:'Advance to Expert & Beyond!', message:'Amazing! Your Intermediate avg is '+levelAvg+'/100. Expert & Beyond is now unlocked!', badgeClass:'diff-hard' };
          else if (levelAvg >= 50)
            rec = { label:'Good Progress', message:'Keep it up! Reach avg ≥ 70 to unlock Expert & Beyond (current: '+levelAvg+'/100).', badgeClass:'diff-medium' };
          else
            rec = { label:'Stay at Intermediate', message:'Focus on speed and accuracy. Target avg ≥ 70 to unlock Expert & Beyond.', badgeClass:'diff-medium' };
        } else { // expert
          if (levelAvg >= 75)
            rec = { label:'Expert & Beyond — Outstanding!', message:'Your Expert & Beyond performance is excellent (avg '+levelAvg+'/100). GBA is tracking your ability!', badgeClass:'diff-hard' };
          else if (levelAvg >= 50)
            rec = { label:'Keep Improving', message:'Good Expert & Beyond progress (avg '+levelAvg+'/100). GBA is adjusting difficulty automatically.', badgeClass:'diff-medium' };
          else
            rec = { label:'Expert & Beyond — Focus on Accuracy', message:'This level is challenging. GBA is measuring your ability — focus on accuracy first.', badgeClass:'diff-easy' };
        }
        this.diffRecommendation = rec;
        this.backspaceCount = 0;

        // GBA: update ability estimate from server
        if (j.theta !== undefined) {
          this.gbaTheta    = j.theta;
          this.gbaLdNext   = j.ld_next;
          this.showGbaCard = true;
        }
      })
      .catch(()=>{ this.error='Submit failed — please try again.'; });
    },

    /* ── Wikipedia image lookup ── */
    async fetchWikiImage(word, def) {
      this.wikiModal = { open: true, loading: true, image: null, title: word, def: def, error: '' };
      try {
        const q = encodeURIComponent(word);
        const url = `https://en.wikipedia.org/w/api.php?action=query&generator=search&gsrsearch=${q}&prop=pageimages&format=json&pithumbsize=500&origin=*&gsrlimit=5`;
        const res  = await fetch(url);
        const data = await res.json();
        const pages = data.query?.pages;
        if (!pages) {
          this.wikiModal.error = 'No image found for this word.';
          this.wikiModal.loading = false;
          return;
        }
        const sorted  = Object.values(pages).sort((a, b) => (a.index || 0) - (b.index || 0));
        const withImg = sorted.find(p => p.thumbnail?.source);
        if (withImg) {
          this.wikiModal.image = withImg.thumbnail.source;
          this.wikiModal.title = withImg.title;
        } else {
          this.wikiModal.error = 'No image found for this word.';
        }
      } catch {
        this.wikiModal.error = 'Failed to load image. Check your connection.';
      }
      this.wikiModal.loading = false;
    }
  };
}
</script>

<script>
/* ═══════════════════════════════════════════
   Pan-and-zoom for the crossword grid
   — touch only (mobile ≤ 640 px)
   — pinch: zoom in/out (0.6× – 3.5×)
   — single-finger drag: pan when zoomed in
   — "Reset Zoom" button snaps back to 1×
═══════════════════════════════════════════ */
(function () {
  /* Only activate on touch-capable / narrow screens */
  if (!('ontouchstart' in window)) return;

  let fitScale = 1;          /* scale that makes the grid fit the viewport width */
  let scale = 1, panX = 0, panY = 0;
  const MAX = 3.5;

  let isPinching  = false, lastDist = 0;
  let isSinglePan = false, spStartX = 0, spStartY = 0, spPanX0 = 0, spPanY0 = 0;

  const vp  = () => document.getElementById('cw-panzoom-viewport');
  const inn = () => document.getElementById('cw-panzoom-inner');
  const rb  = () => document.getElementById('cw-zoom-reset');

  function applyTransform() {
    const el = inn(); if (!el) return;
    el.style.transform = `translate(${panX}px,${panY}px) scale(${scale})`;
    const btn = rb();
    if (btn) btn.style.display =
      (scale > fitScale * 1.08 || Math.abs(panX) > 2 || Math.abs(panY) > 2) ? 'block' : 'none';
  }

  function clampPan() {
    const v = vp(), el = inn(); if (!v || !el) return;
    const vpW = v.clientWidth, vpH = v.clientHeight;
    const natW = el.offsetWidth, natH = el.offsetHeight;
    const limX = Math.max(0, (natW * scale - vpW) / 2);
    const limY = Math.max(0, (natH * scale - vpH) / 2);
    panX = Math.max(-limX, Math.min(limX, panX));
    panY = Math.max(-limY, Math.min(limY, panY));
    if (natW * scale <= vpW) panX = 0;
    if (natH * scale <= vpH) panY = 0;
  }

  /* Compute scale that exactly fits the grid's natural width into the viewport */
  function computeFitScale() {
    const v = vp(), el = inn();
    if (!v || !el) return 1;
    const saved = el.style.transform;
    el.style.transform = 'none';       /* strip transform to measure natural size */
    const natW = el.offsetWidth;
    el.style.transform = saved;
    const vpW = v.clientWidth;
    if (!vpW || !natW) return 1;
    return Math.min(1, (vpW - 16) / natW);   /* 8 px breathing room each side */
  }

  /* Auto-fit: shrink grid to fill viewport width, reset pan */
  function fitToViewport() {
    fitScale = computeFitScale();
    scale = fitScale; panX = 0; panY = 0;
    applyTransform();
  }

  function touchDist(t) {
    return Math.hypot(t[1].clientX - t[0].clientX, t[1].clientY - t[0].clientY);
  }

  document.addEventListener('touchstart', function (e) {
    const v = vp(); if (!v || !v.contains(e.target)) return;
    if (e.touches.length === 2) {
      isPinching = true; isSinglePan = false;
      lastDist = touchDist(e.touches);
      e.preventDefault();
    } else if (e.touches.length === 1 && scale > fitScale * 1.1) {
      isSinglePan = true;
      spStartX = e.touches[0].clientX; spStartY = e.touches[0].clientY;
      spPanX0 = panX; spPanY0 = panY;
    }
  }, { passive: false });

  document.addEventListener('touchmove', function (e) {
    const v = vp(); if (!v || !v.contains(e.target)) return;
    if (isPinching && e.touches.length >= 2) {
      e.preventDefault();
      const nd = touchDist(e.touches);
      scale    = Math.min(MAX, Math.max(fitScale * 0.95, scale * (nd / lastDist)));
      lastDist = nd;
      clampPan(); applyTransform();
    } else if (isSinglePan && e.touches.length === 1 && scale > fitScale * 1.1) {
      e.preventDefault();
      panX = spPanX0 + (e.touches[0].clientX - spStartX);
      panY = spPanY0 + (e.touches[0].clientY - spStartY);
      clampPan(); applyTransform();
    }
  }, { passive: false });

  document.addEventListener('touchend', function (e) {
    if (e.touches.length < 2) isPinching  = false;
    if (e.touches.length === 0) {
      isSinglePan = false;
      if (scale < fitScale * 0.96) fitToViewport();   /* snap back if over-pinched */
    }
  });

  /* Global handles: called from Alpine generate() and from reset button */
  window.cwResetZoom = function () { fitToViewport(); };
  window.cwFitGrid   = fitToViewport;

  /* Fallback observer: catches any case where Alpine renders the grid
     outside the normal generate() flow (e.g. hot-reload, back-nav). */
  const bodyObs = new MutationObserver(function () {
    const el = inn();
    if (!el || el._pzRO) return;
    el._pzRO = new ResizeObserver(function () {
      requestAnimationFrame(fitToViewport);
    });
    el._pzRO.observe(el);
    requestAnimationFrame(function () { requestAnimationFrame(fitToViewport); });
  });
  document.addEventListener('DOMContentLoaded', function () {
    bodyObs.observe(document.body, { childList: true, subtree: true });
  });
}());
</script>

</x-app-layout>