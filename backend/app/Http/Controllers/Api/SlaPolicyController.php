<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSlaPolicyRequest;
use App\Http\Resources\SlaPolicyResource;
use App\Models\SlaPolicy;
use Illuminate\Support\Facades\Auth;

class SlaPolicyController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return SlaPolicyResource::collection(SlaPolicy::all());
    }

    public function update(UpdateSlaPolicyRequest $request, SlaPolicy $slaPolicy)
    {
        $slaPolicy->update($request->validated());

        return new SlaPolicyResource($slaPolicy);
    }
}
