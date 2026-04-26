<?php

namespace App\Livewire\Studio;

use App\Models\Branch\SupportTicket;
use App\Models\Organization\Branch;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StudioSupport extends Component
{
    use WithPagination;

    // ─── Filters (Task 2.1) ───
    public string $statusFilter = '';
    public string $priorityFilter = '';
    public string $categoryFilter = '';
    public string $branchFilter = '';
    public string $search = '';

    // ─── Ticket Detail / Response (Task 2.2, 2.3) ───
    public ?int $selectedTicketId = null;
    public string $responseNote = '';
    public string $newStatus = '';

    // ─── Pagination ───
    public int $perPage = 15;

    // ─── Status Transition State Machine (Task 2.4) ───
    private const VALID_TRANSITIONS = [
        'open' => 'in_progress',
        'in_progress' => 'resolved',
        'resolved' => 'closed',
    ];

    /**
     * Reset all filter properties.
     * Task 2.1
     */
    public function resetFilters(): void
    {
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->categoryFilter = '';
        $this->branchFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Load ticket with relationships for detail view.
     * Task 2.2
     */
    public function selectTicket(int $ticketId): void
    {
        $this->selectedTicketId = $ticketId;
        $this->responseNote = '';
        $this->newStatus = '';
    }

    /**
     * Close the detail panel and reset related properties.
     * Task 2.2
     */
    public function closeDetail(): void
    {
        $this->selectedTicketId = null;
        $this->responseNote = '';
        $this->newStatus = '';
    }

    /**
     * Submit a response note to the selected ticket.
     * Task 2.3
     */
    public function submitResponse(): void
    {
        $this->validate([
            'responseNote' => 'required|string|min:5',
        ]);

        $ticket = SupportTicket::findOrFail($this->selectedTicketId);
        $ticket->response_note = $this->responseNote;
        $ticket->it_responder_id = auth()->id();
        $ticket->responded_at = now();
        $ticket->save();

        $this->responseNote = '';
        session()->flash('success', 'Response submitted successfully.');
    }

    /**
     * Transition ticket status with state machine validation.
     * Task 2.4
     */
    public function updateStatus(string $status): void
    {
        $ticket = SupportTicket::findOrFail($this->selectedTicketId);
        $allowed = self::VALID_TRANSITIONS[$ticket->status] ?? null;

        if ($allowed !== $status) {
            $this->addError('status', "Invalid transition. Allowed: {$ticket->status} → {$allowed}");
            return;
        }

        $ticket->status = $status;

        if ($status === 'resolved') {
            $ticket->resolved_at = now();
        }

        $ticket->save();
        session()->flash('success', "Ticket status updated to {$status}.");
    }

    /**
     * Render the component with filtered tickets and analytics.
     * Task 2.1
     */
    public function render()
    {
        // Build filtered query
        $query = SupportTicket::with(['user', 'responder', 'branch']);

        if ($this->statusFilter !== '') {
            $query->forStatus($this->statusFilter);
        }

        if ($this->priorityFilter !== '') {
            $query->forPriority($this->priorityFilter);
        }

        if ($this->categoryFilter !== '') {
            $query->forCategory($this->categoryFilter);
        }

        if ($this->branchFilter !== '') {
            $query->forBranch((int) $this->branchFilter);
        }

        if ($this->search !== '') {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Analytics
        $openCount = SupportTicket::open()->count();
        $resolvedCount = SupportTicket::whereIn('status', ['resolved', 'closed'])->count();

        $avgResolutionTime = SupportTicket::whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->avg(DB::raw("(julianday(resolved_at) - julianday(created_at)) * 24"));

        $priorityBreakdown = SupportTicket::open()
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $branchBreakdown = SupportTicket::open()
            ->selectRaw('branch_id, count(*) as count')
            ->groupBy('branch_id')
            ->with('branch')
            ->get();

        $categoryBreakdown = SupportTicket::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        // Selected ticket for detail view
        $selectedTicket = $this->selectedTicketId
            ? SupportTicket::with(['user', 'responder', 'branch'])->find($this->selectedTicketId)
            : null;

        return view('livewire.studio.support', [
            'tickets' => $tickets,
            'openCount' => $openCount,
            'resolvedCount' => $resolvedCount,
            'avgResolutionTime' => round($avgResolutionTime ?? 0, 1),
            'priorityBreakdown' => $priorityBreakdown,
            'branchBreakdown' => $branchBreakdown,
            'categoryBreakdown' => $categoryBreakdown,
            'selectedTicket' => $selectedTicket,
            'branches' => Branch::orderBy('name')->get(),
        ])->layout('layouts.studio');
    }
}
