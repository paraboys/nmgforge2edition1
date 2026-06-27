<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index()
    {
        return TagResource::collection(Tag::all());
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());
        return new TagResource($tag);
    }

    public function update(StoreTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());
        return new TagResource($tag);
    }

    public function destroy(Tag $tag)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $tag->delete();
        return response()->noContent();
    }
}
