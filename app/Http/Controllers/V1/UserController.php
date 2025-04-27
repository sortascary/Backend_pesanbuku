<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\LoginRequest;
use App\Http\Requests\V1\User\RegisterRequest;
use App\Http\Requests\V1\User\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserCollection;

class UserController extends Controller
{
    public function index()
    {
        return new UserCollection(User::all());
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Validasi distributor_key
        if ($data['role'] === 'distributor') {
            $validKey = 'KEY123';
            if (!isset($data['distributor_key']) || $data['distributor_key'] !== $validKey) {
                return response()->json([
                    'message' => 'Distributor Key tidak valid'
                ], 403);
            }
        }
        
        unset($data['distributor_key']);

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'FCMToken' => $data['FCMToken'] ?? null,
            'daerah' => $data['role'] === 'sekolah' ? $data['daerah'] ?? null : null,
            'schoolName' => $data['role'] === 'sekolah' ? $data['schoolName'] ?? null : null,
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!Auth::attempt(['phone' => $data['phone'], 'password' => $data['password']])) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('phone', $data['phone'])->first();

        if (isset($data['FCMToken'])) {
            $user->FCMToken = $data['FCMToken'];
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => new UserResource($user),
            'token' => $token
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.'
        ], 200);
    }

    public function show(string $id)
    {
        $user = User::find($id);
        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $user->update($data);
            return response()->json([
                'message' => 'User updated successfully',
                'data' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
