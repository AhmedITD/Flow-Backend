<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class SearchTicketsAction
{
    /**
     * Search tickets based on criteria.
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, Ticket>
     */
    public function execute(array $criteria): Collection
    {
        $query = Ticket::query();

        if (isset($criteria['tenant_id'])) {
            $query->where('tenant_id', $criteria['tenant_id']);
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }

        if (isset($criteria['category'])) {
            $query->where('category', $criteria['category']);
        }

        if (isset($criteria['assigned_to'])) {
            $query->where('assigned_to', $criteria['assigned_to']);
        }

        $limit = $criteria['limit'] ?? 10;
        
        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Format search results as a readable string.
     *
     * @param  Collection<int, Ticket>  $tickets
     */
    public function formatResults(Collection $tickets): string
    {
        if ($tickets->isEmpty()) {
            return 'No tickets found matching the search criteria.';
        }

        $results = "Found {$tickets->count()} ticket(s):\n\n";
        foreach ($tickets as $ticket) {
            $results .= "- **{$ticket->ticket_id}**: {$ticket->subject} ({$ticket->status}, {$ticket->priority})\n";
        }

        return $results;
    }
}

