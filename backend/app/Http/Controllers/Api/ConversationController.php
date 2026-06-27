<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role === 'customer' && $ticket->requester_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = $ticket->conversations()->with('user')->latest();

        if (Auth::user()->role === 'customer') {
            $query->where('is_internal', false);
        }

        return ConversationResource::collection($query->get());
    }

    public function store(StoreConversationRequest $request, Ticket $ticket)
    {
        if (Auth::user()->role === 'customer' && $ticket->requester_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        
        // Customers cannot create internal notes
        if (Auth::user()->role === 'customer') {
            $validated['is_internal'] = false;
        }

        $conversation = $ticket->conversations()->create(array_merge($validated, [
            'user_id' => Auth::id(),
            'organization_id' => Auth::user()->organization_id,
        ]));

        return new ConversationResource($conversation->load('user'));
    }
}
