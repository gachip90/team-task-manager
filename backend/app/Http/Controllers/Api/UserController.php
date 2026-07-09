<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->whereIn('id', [2, 3])
            ->where('role', 'user')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }
}
