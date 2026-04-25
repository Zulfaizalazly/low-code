<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Kernel\Audit\AuditLog;
use Illuminate\Http\Request;

class ViewToggleController extends Controller
{
    public function toggle(Request $request)
    {
        // Only branch managers can toggle
        if (!auth()->user()->hasRole('branch_manager')) {
            abort(403, 'Unauthorized action.');
        }

        $user = $request->user();
        $currentMode = session('branch_view_mode', 'ops');
        
        if ($currentMode === 'ops') {
            session(['branch_view_mode' => 'staff']);

            try {
                AuditLog::record(
                    action: 'STAFF_VIEW_ENTERED',
                    branchId: $user->branch_id,
                    description: "{$user->name} entered Staff View mode — now operating with staff-level capabilities.",
                    payload: [
                        'from_mode'  => 'ops',
                        'to_mode'    => 'staff',
                        'session_id' => session()->getId(),
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Audit trail failed: ' . $e->getMessage());
            }

            return redirect()->route('runtime.portal');
        } else {
            session(['branch_view_mode' => 'ops']);

            try {
                AuditLog::record(
                    action: 'STAFF_VIEW_EXITED',
                    branchId: $user->branch_id,
                    description: "{$user->name} returned to Ops View — staff capabilities deactivated.",
                    payload: [
                        'from_mode'  => 'staff',
                        'to_mode'    => 'ops',
                        'session_id' => session()->getId(),
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Audit trail failed: ' . $e->getMessage());
            }

            return redirect()->route('branch.dashboard');
        }
    }
}
