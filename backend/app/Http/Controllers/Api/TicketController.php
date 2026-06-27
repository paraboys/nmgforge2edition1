<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TicketFilterRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketCollection;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(protected TicketService $ticketService) {}

    public function index(TicketFilterRequest $request)
    {
        $tickets = $this->ticketService->getTicketsForUser(
            $request->user(),
            $request->validated()
        );

        return new TicketCollection($tickets);
    }

    public function store(StoreTicketRequest $request)
    {
        if ($request->has('requester_id') && ! ($request->user()->isAdmin() || $request->user()->isAgent())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket = $this->ticketService->create($request->validated(), $request->user());

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $ticket = Ticket::withoutGlobalScopes()->find($id);

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('view', $ticket);

        return new TicketResource($ticket->load(['requester', 'assignee', 'comments.author']));
    }

    public function update(UpdateTicketRequest $request, int $id)
    {
        $ticket = Ticket::withoutGlobalScopes()->find($id);

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('update', $ticket);
        $ticket = $this->ticketService->update($ticket, $request->validated(), $request->user());
        return new TicketResource($ticket);
    }

    public function destroy(int $id)
    {
        $ticket = Ticket::withoutGlobalScopes()->find($id);

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('delete', $ticket);
        $ticket->delete();
        return response()->noContent();
    }

    public function assign(Request $request, int $id)
    {
        $ticket = Ticket::withoutGlobalScopes()->find($id);

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('assign', $ticket);
        $request->validate(['assignee_id' => 'nullable|integer|exists:users,id']);
        $ticket = $this->ticketService->assign($ticket, $request->assignee_id, $request->user());
        return new TicketResource($ticket);
    }
}
