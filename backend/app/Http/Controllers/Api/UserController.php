<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = User::query();

        if ($user->isCustomer()) {
            $query->where('id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where('organization_id', $user->organization_id)
                ->where('role', 'customer');
        } else {
            $query->where('organization_id', $user->organization_id);
        }

        return UserResource::collection($query->paginate(15));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return new UserResource($user);
    }
}
