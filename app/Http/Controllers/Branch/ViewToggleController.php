<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
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

            // Log: Manager entered Staff View
            try {
                AuditTrail::create([
                    'user_id'     => $user->id,
                    'branch_id'   => $user->branch_id,
                    'action'      => 'STAFF_VIEW_ENTERED',
                    'description' => "{$user->name} entered Staff View mode — now operating with staff-level capabilities.",
                    'payload'     => [
                        'from_mode'  => 'ops',
                        'to_mode'    => 'staff',
                        'session_id' => session()->getId(),
                    ],
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Audit trail failed: ' . $e->getMessage());
            }

            return redirect()->route('runtime.portal');
        } else {
            session(['branch_view_mode' => 'ops']);

            // Log: Manager returned to Ops View
            try {
                AuditTrail::create([
                    'user_id'     => $user->id,
                    'branch_id'   => $user->branch_id,
                    'action'      => 'STAFF_VIEW_EXITED',
                    'description' => "{$user->name} returned to Ops View — staff capabilities deactivated.",
                    'payload'     => [
                        'from_mode'  => 'staff',
                        'to_mode'    => 'ops',
                        'session_id' => session()->getId(),
                    ],
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Audit trail failed: ' . $e->getMessage());
            }

            return redirect()->route('branch.dashboard');
        }
    }
}
