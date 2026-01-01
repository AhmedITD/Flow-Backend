<?php

namespace App\Mcp\Prompts\CallCenter;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class EscalateTicketPrompt extends Prompt
{
    /**
     * The prompt's description.
     */
    protected string $description = <<<'MARKDOWN'
        Provides guidance on when and how to escalate tickets to specialized teams or higher-level support. Use this when an issue requires expertise beyond the current agent's capabilities.
    MARKDOWN;

    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $ticketId = $request->get('ticket_id');
        $reason = $request->get('reason', 'Requires specialized attention');
        $priority = $request->get('priority', 'high');

        $prompt = <<<MARKDOWN
# Escalating Ticket

**Ticket ID:** {$ticketId}
**Escalation Reason:** {$reason}
**Priority:** {$priority}

## Escalation Process:

1. **Verify Escalation is Necessary**:
   - Issue is beyond your authority or expertise
   - Customer has requested escalation
   - Issue requires specialized knowledge
   - Customer satisfaction is at risk

2. **Prepare Ticket for Escalation**:
   - Update ticket summary with all relevant details
   - Increase priority if needed (use UpdateTicketTool)
   - Add escalation reason to ticket notes
   - Ensure all customer information is complete

3. **Assign to Appropriate Team**:
   - Use AssignTicketTool to assign to specialist
   - Consider team expertise: billing, technical, shipping, etc.
   - Update ticket status to "pending" if awaiting response

4. **Communicate with Customer**:
   - Inform customer that ticket is being escalated
   - Provide expected resolution time
   - Give customer the ticket reference number
   - Set clear expectations

5. **Follow Up**:
   - Monitor ticket progress
   - Ensure timely response from escalated team
   - Update customer if needed

## Escalation Guidelines:
- **Billing Issues**: Escalate to billing/accounting team
- **Technical Problems**: Escalate to technical support specialists
- **Shipping Issues**: Escalate to logistics/shipping team
- **Account Problems**: Escalate to account management
- **Urgent Matters**: Escalate immediately with high priority

## Next Steps:
1. Use UpdateTicketStatusTool to set status to "pending"
2. Use AssignTicketTool to assign to appropriate agent/team
3. Update ticket summary with escalation details
MARKDOWN;

        return Response::text($prompt);
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, \Laravel\Mcp\Server\Prompts\Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('ticket_id', 'The ticket ID to escalate', true),
            new Argument('reason', 'Reason for escalation', false),
            new Argument('priority', 'Priority level for escalated ticket (low, medium, high, urgent)', false),
        ];
    }
}
