<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayerGbaLog;

class AssessmentController extends Controller
{
    public function index(Request $req)
    {
        $userId = $req->user()->id;

        $spellingLogs = PlayerGbaLog::where('user_id', $userId)
            ->where('game', 'spelling')
            ->orderBy('level_num')
            ->get(['level_num', 'theta', 'ld', 'ld_next', 'success', 'created_at']);

        $crosswordLogs = PlayerGbaLog::where('user_id', $userId)
            ->where('game', 'crossword')
            ->orderBy('level_num')
            ->get(['level_num', 'theta', 'ld', 'ld_next', 'success', 'created_at']);

        $currentThetaSpelling  = $spellingLogs->last()?->theta  ?? 0.30;
        $currentThetaCrossword = $crosswordLogs->last()?->theta ?? 0.30;

        return view('assessment.dashboard', compact(
            'spellingLogs',
            'crosswordLogs',
            'currentThetaSpelling',
            'currentThetaCrossword'
        ));
    }
}
