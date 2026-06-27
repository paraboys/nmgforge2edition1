<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Ticket;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService) {}

    public function index(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $query = $ticket->comments()->with('author')->latest();

        if (auth()->user()->isCustomer()) {
            $query->where('is_internal', false);
        }

        return CommentResource::collection($query->paginate(15));
    }

    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        $this->authorize('create', $ticket);
        $comment = $this->commentService->create($ticket, $request->validated(), $request->user());
        return new CommentResource($comment->load('author'));
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->noContent();
    }
}
