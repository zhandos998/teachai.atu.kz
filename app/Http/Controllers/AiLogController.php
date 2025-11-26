<?php

namespace App\Http\Controllers;

use App\Models\AiLog;
use Inertia\Inertia;

class AiLogController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/AiLogs/Index', [
            'logs' => AiLog::orderBy('id', 'desc')->paginate(20)
        ]);
    }

    public function show($id)
    {
        $log = AiLog::findOrFail($id);

        return Inertia::render('Admin/AiLogs/Show', [
            'log' => $log
        ]);
    }
}
