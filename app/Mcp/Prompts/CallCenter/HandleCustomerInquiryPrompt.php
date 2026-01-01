<?php

namespace App\Mcp\Prompts\CallCenter;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class HandleCustomerInquiryPrompt extends Prompt
{
    /**
     * The prompt's description.
     */
    protected string $description = <<<'MARKDOWN'
        Provides a structured approach for handling customer inquiries during a call. This prompt guides the agent through gathering information, understanding the issue, and creating appropriate tickets.
    MARKDOWN;

    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $inquiry = $request->get('inquiry');
        $customerName = $request->get('customer_name', 'Customer');
        $tenantId = $request->get('tenant_id');

        $prompt = <<<MARKDOWN
# Handling Customer Inquiry

**Customer:** {$customerName}
**Tenant ID:** {$tenantId}
**Inquiry:** {$inquiry}

## Steps to Handle This Inquiry:

1. **Listen Actively**: Pay full attention to the customer's concern and take notes.

2. **Gather Information**:
   - Confirm customer details (name, account number if applicable)
   - Understand the full scope of the issue
   - Ask clarifying questions if needed
   - Note any previous ticket history

3. **Categorize the Issue**:
   - Determine the category: billing, technical, shipping, account, general, or other
   - Assess the priority: low, medium, high, or urgent
   - Consider the impact on the customer

4. **Create or Update Ticket**:
   - Use the CreateTicketTool if this is a new issue
   - Use SearchTicketsTool to check for related tickets
   - Use UpdateTicketTool if updating an existing ticket

5. **Provide Solution or Next Steps**:
   - If you can resolve immediately, do so and update ticket status
   - If escalation is needed, use the EscalateTicketPrompt
   - Set appropriate expectations with the customer

6. **Follow Up**:
   - Document all actions taken
   - Update ticket status appropriately
   - Ensure customer has a ticket reference number

## Key Reminders:
- Be empathetic and professional
- Document everything in the ticket summary
- Set realistic expectations
- Follow up as promised
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
            new Argument('inquiry', 'The customer inquiry or issue description', true),
            new Argument('customer_name', 'The name of the customer', false),
            new Argument('tenant_id', 'The tenant ID for the customer', false),
        ];
    }
}
