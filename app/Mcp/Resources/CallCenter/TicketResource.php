<?php

namespace App\Mcp\Resources\CallCenter;

use App\Actions\ViewTicketAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

class TicketResource extends Resource implements HasUriTemplate
{
    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Provides access to ticket data. Use this resource to retrieve ticket information by ticket ID.
    MARKDOWN;

    /**
     * Get the URI template for this resource.
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('ticket://{ticket_id}');
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $ticketId = $request->get('ticket_id');

        if (! $ticketId) {
            return Response::error('Ticket ID is required.');
        }

        try {
            $action = new ViewTicketAction();
            $ticket = $action->execute($ticketId);
            $content = $action->formatDetails($ticket);

            return Response::text($content);
        } catch (ModelNotFoundException $e) {
            return Response::error($e->getMessage());
        }
    }
}
