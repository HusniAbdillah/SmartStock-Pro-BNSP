<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ErrorLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ErrorLog::orderByDesc('created_at');

        if ($severity = $request->get('severity')) {
            $query->ofSeverity($severity);
        }

        if ($request->get('unresolved')) {
            $query->unresolved();
        }

        $errorLogs = $query->paginate(25)->withQueryString();

        $counts = [
            'total'    => ErrorLog::count(),
            'critical' => ErrorLog::ofSeverity('critical')->unresolved()->count(),
            'warning'  => ErrorLog::ofSeverity('warning')->unresolved()->count(),
            'info'     => ErrorLog::ofSeverity('info')->unresolved()->count(),
        ];

        return view('error-logs.index', compact('errorLogs', 'counts'));
    }

    public function resolve(ErrorLog $errorLog): RedirectResponse
    {
        $errorLog->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Log error ditandai sebagai telah diselesaikan.');
    }
}
