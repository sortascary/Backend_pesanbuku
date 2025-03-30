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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new UserCollection(User::all());
    }

    public function register(RegisterRequest $request) : JsonResponse
    {
        $data = $request->validated();

        $user = new User($data);

        if (User::where('phone', $user->phone)->exists()) { 
            return response()->json([
                'message' => 'Phone number already in use'
            ], 409);
        }else{
            $userpost = User::create([
                'name' => $user->name,
                'phone' => $user->phone,
                'daerah' => $user->daerah,
                'schoolName' => $user->schoolName,
                'role' => $user->role,
                'FCMToken' => $user->FCMToken,
                'password' => Hash::make($data['password']),
            ]);
    
            return response()->json([
                'message' => 'User created successfully',
                'data' => new UserResource($userpost),
                'token' => $userpost->createToken('auth_token')->plainTextToken,
            ], 201);
        }        
    }

    public function Login(LoginRequest $request): JsonResponse
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

        // Generate authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => new UserResource($user),
            'token' => $token
        ], 200);
    }

    public function logout(Request $request) : JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'logout sucessful.'
        ], 200  );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try{

            $user->update($data);

            return response()->json([
                'message' => 'User updated successfully', 
                'order' => new UserResource($user)
            ]);

        } catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
