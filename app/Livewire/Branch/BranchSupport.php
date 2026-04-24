<?php

namespace App\Livewire\Branch;

use App\Models\Branch\SupportTicket;
use Livewire\Component;

class BranchSupport extends Component
{
    /**
     * IT Department Communication (Req 9)
     *
     * Features:
     * - Submit new support ticket (title, description, category, priority)
     * - View open/closed tickets with status tracking
     * - IT contact info display
     * - Ticket status timeline
     */

    // New ticket form
    public bool $showNewTicket = false;
    public string $ticketTitle = '';
    public string $ticketDescription = '';
    public string $ticketCategory = 'issue';
    public string $ticketPriority = 'medium';

    // Filter
    public string $filter = 'open';

    public function openNewTicket(): void
    {
        $this->reset(['ticketTitle', 'ticketDescription', 'ticketCategory', 'ticketPriority']);
        $this->ticketCategory = 'issue';
        $this->ticketPriority = 'medium';
        $this->showNewTicket = true;
    }

    public function submitTicket(): void
    {
        $this->validate([
            'ticketTitle' => 'required|string|min:5|max:255',
            'ticketDescription' => 'required|string|min:10',
            'ticketCategory' => 'required|in:bug,feature_request,issue',
            'ticketPriority' => 'required|in:low,medium,high,critical',
        ]);

        SupportTicket::create([
            'user_id' => auth()->id(),
            'branch_id' => auth()->user()->branch_id,
            'title' => $this->ticketTitle,
            'description' => $this->ticketDescription,
            'category' => $this->ticketCategory,
            'priority' => $this->ticketPriority,
            'status' => 'open',
        ]);

        $this->showNewTicket = false;
        session()->flash('success', 'Support ticket submitted successfully. IT team will be notified.');
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render()
    {
        $user = auth()->user();

        $ticketsQuery = SupportTicket::where('user_id', $user->id);
        if ($this->filter === 'open') {
            $ticketsQuery = $ticketsQuery->open();
        } elseif ($this->filter === 'resolved') {
            $ticketsQuery = $ticketsQuery->whereIn('status', ['resolved', 'closed']);
        }
        $tickets = $ticketsQuery->latest()->get();

        $openCount = SupportTicket::forUser($user->id)->open()->count();
        $resolvedCount = SupportTicket::forUser($user->id)->whereIn('status', ['resolved', 'closed'])->count();

        return view('livewire.branch.support', [
            'tickets' => $tickets,
            'openCount' => $openCount,
            'resolvedCount' => $resolvedCount,
            'itSupport' => config('branch.it_support'),
        ])->layout('layouts.branch');
    }
}
