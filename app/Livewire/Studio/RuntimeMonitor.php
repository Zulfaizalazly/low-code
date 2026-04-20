<?php

namespace App\Livewire\Studio;

use App\Runtime\Models\AutomationExecutionLog;
use Livewire\Component;
use Livewire\WithPagination;

class RuntimeMonitor extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.studio.runtime-monitor', [
            'executions' => AutomationExecutionLog::latest()
                ->with(['nodeLogs'])
                ->paginate(15),
            'stats' => [
                'total' => AutomationExecutionLog::count(),
                'success' => AutomationExecutionLog::where('status', 'completed')->count(),
                'failed' => AutomationExecutionLog::where('status', 'failed')->count(),
            ]
        ])->layout('layouts.studio');
    }
}
