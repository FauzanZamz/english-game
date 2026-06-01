```
<x-app-layout>
<div class="max-w-4xl mx-auto p-6">

    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            🏆 Leaderboard: 
            <span class="text-blue-600">{{ $game->name }}</span>
        </h2>

        <table class="min-w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gradient-to-r from-blue-50 to-purple-50 text-gray-700">
                    <th class="p-3 border font-semibold text-left">Rank</th>
                    <th class="p-3 border font-semibold text-left">Player</th>
                    <th class="p-3 border font-semibold text-center">Score</th>
                    <th class="p-3 border font-semibold text-center">Time (s)</th>
                    <th class="p-3 border font-semibold text-center">Date</th>
                </tr>
            </thead>

            <tbody>
            @foreach ($top as $i => $p)
                @php
                    $rank = $i + 1;
                    $rankColor = match($rank) {
                        1 => 'text-yellow-500',
                        2 => 'text-gray-400',
                        3 => 'text-orange-500',
                        default => 'text-gray-500',
                    };
                @endphp

                <tr class="transition hover:bg-blue-50/70 hover:scale-[1.01]">
                    <!-- Rank -->
                    <td class="p-3 border font-bold {{ $rankColor }}">
                        @if($rank == 1) 🥇 @elseif($rank == 2) 🥈 @elseif($rank == 3) 🥉 @else #{{ $rank }} @endif
                    </td>

                    <!-- Username + Avatar -->
                    <td class="p-3 border flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                            {{ strtoupper(substr($p->user->name, 0, 1)) }}
                        </div>
                        <span class="font-medium">{{ $p->user->name }}</span>
                    </td>

                    <!-- Score -->
                    <td class="p-3 border text-center font-bold text-blue-700">
                        {{ $p->score }}
                    </td>

                    <!-- Time -->
                    <td class="p-3 border text-center">
                        {{ $p->duration_sec }}
                    </td>

                    <!-- Date -->
                    <td class="p-3 border text-center text-gray-600">
                        {{ $p->created_at->format('Y-m-d H:i') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>

```