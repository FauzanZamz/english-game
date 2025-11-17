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
        <select x-model="level" class="mt-1 border rounded-xl px-3 py-2 bg-white/90 focus:ring-sky-300 focus:border-sky-400">
          <option value="beginner">Beginner</option>
          <option value="expert">Expert</option>
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
                       class="flex-1 rounded-xl border px-3 py-2 focus:ring-sky-300 focus:border-sky-400 bg-white/90">
                <button @click="submit()" class="rounded-xl px-4 py-2 bg-emerald-500 text-white hover:bg-emerald-600">✅ Jawab</button>
                <button @click="giveup()" class="rounded-xl px-4 py-2 bg-rose-500 text-white hover:bg-rose-600">🏳️ Menyerah</button>
              </div>
              <div class="mt-2 text-sm font-medium" :class="{'text-emerald-700': feedback.startsWith('✅'), 'text-rose-700': feedback.startsWith('❌')}" x-text="feedback"></div>
            </div>
          </template>

          <template x-if="!roundActive && roundIdx===0">
            <p class="text-sky-900/70">Klik <b>Mulai</b> untuk memulai ronde pertama.</p>
          </template>
        </div>
      </div>

      <!-- Right: wiki (selalu tampil per ronde) -->
      <div class="space-y-4">
        <div class="rounded-2xl bg-white/90 backdrop-blur border shadow p-5">
          <h3 class="font-bold text-sky-900">Explanation</h3>
          <p class="text-sky-900/80 mt-1" x-text="wiki.extract"></p>
          <img :src="wiki.image" x-show="wiki.image" class="mt-3 rounded-xl shadow">
        </div>

        <template x-if="sessionDone">
          <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
            <p class="font-semibold text-emerald-700">Sesi selesai!</p>
            <a href="{{ route('leaderboard.spelling') }}" class="inline-block mt-2 px-4 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700">Lihat Leaderboard</a>
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
    answer: '', feedback: '', roundActive: false, sessionDone: false, t0: null,

    start(){ this.roundIdx=0; this.score=0; this.sessionDone=false; this.t0=Date.now(); this.nextRound(); },

    nextRound(){
      if (this.roundIdx>=5){ this.finishSession(); return; }
      this.answer=''; this.feedback=''; this.roundActive=true;
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
        this.score+=j.scoreDelta; this.feedback = j.correct?'✅ Benar!':'❌ Salah. Jawaban: '+j.expected;
        this.roundActive=false; this.roundIdx++; setTimeout(()=>this.nextRound(), 900);
      })
    },

    giveup(){
      fetch('{{ route('spelling.answer') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({answer:'', giveup:true})})
      .then(r=>r.json()).then(j=>{
        this.feedback = 'Menyerah. Kata: '+j.expected; this.roundActive=false; this.roundIdx++; setTimeout(()=>this.nextRound(), 900);
      })
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
      const dur = Math.round((Date.now()-this.t0)/1000);
      fetch('{{ route('spelling.finish') }}',{method:'POST', headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'
      }, body: JSON.stringify({duration_sec: dur})});
    }
  }
}
</script>
</x-app-layout>
