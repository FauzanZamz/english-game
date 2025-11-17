<x-app-layout>
<div class="max-w-3xl mx-auto p-4">
  <h2 class="text-xl font-bold">Leaderboard: {{ $game->name }}</h2>
  <table class="min-w-full mt-4 border text-sm">
    <thead><tr>
      <th class="p-2 border">#</th>
      <th class="p-2 border">User</th>
      <th class="p-2 border">Score</th>
      <th class="p-2 border">Time (s)</th>
      <th class="p-2 border">Date</th>
    </tr></thead>
    <tbody>
    @foreach($top as $i=>$p)
      <tr>
        <td class="p-2 border">{{ $i+1 }}</td>
        <td class="p-2 border">{{ $p->user->name }}</td>
        <td class="p-2 border font-bold">{{ $p->score }}</td>
        <td class="p-2 border">{{ $p->duration_sec }}</td>
        <td class="p-2 border">{{ $p->created_at->format('Y-m-d H:i') }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
</x-app-layout>
