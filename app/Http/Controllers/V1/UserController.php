<?php

namespace App\Http\Controllers\V1;

use Illuminate\Support\Facades\Log;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\V1\User\LoginRequest;
use App\Http\Requests\V1\User\RegisterRequest;
use App\Http\Requests\V1\User\UpdateProfileRequest;
use App\Http\Requests\V1\User\PasswordResetRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserCollection;

class UserController extends Controller
{
    public function index()
    {
        return new UserCollection(User::all());
    }

    public function getuserdata(Request $request){
        $user = $request->user();

        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $userexist = User::where('email', $data['email'])->first();

        if ($userexist && $userexist->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah digunakan.',
            ], 409);
        }

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
        
        if ($userexist){
            $userexist->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'role' => $data['role'],
                'daerah' => $data['role'] === 'sekolah' ? $data['daerah'] ?? null : null,
                'schoolName' => $data['role'] === 'sekolah' ? $data['schoolName'] ?? null : null,
                'password' => Hash::make($data['password']),
            ]);
            $user = $userexist;
        } else {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'role' => $data['role'],
                'daerah' => $data['role'] === 'sekolah' ? $data['daerah'] ?? null : null,
                'schoolName' => $data['role'] === 'sekolah' ? $data['schoolName'] ?? null : null,
                'password' => Hash::make($data['password']),
            ]);
        }
        
        $user->sendEmailVerificationNotification();
        
        return response()->json([
            'message' => 'User created successfully. Check your email for verification link.',
            'data' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $data['email'])->first();

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

    public function verify(Request $request, $id, $hash)
    {
        if (!$request->hasValidSignature()) {
            return view('Verify', [
                'message' => 'Invalid or expired link',
                'success' => false
            ]);
        }

        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return view('Verify', [
                'message' => 'User is already verified',
                'success' => false
            ]);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return view('Verify', [
                'message' => 'Invalid credentials/data',
                'success' => false
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return view('Verify', [
            'message' => 'Successfully verified your email',
            'success' => true
        ]);
    }

    public function sendVerification(Request $request){
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }

    public function sendResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email'
        ]);

        
        $status = Password::sendResetLink(['email' => $request->email]);


        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetRedirect(Request $request, $email, $token)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return view('ResetPass', [
                'appUrl' => "*",
                'isValid' => false
            ]);
        }
        $url = "pesanbuku://reset/reset-password?token=$token&email=" . urlencode($email);

        return view('ResetPass', [
            'appUrl' => $url,
            'isValid' => true
        ]);
    }


    public function reset(PasswordResetRequest $request)
    {
        $data = $request->validated();

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$record || !Hash::check($data['token'], $record->token)) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->remember_token = Str::random(60);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return response()->json([
            'message' => 'it worked',
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            if ($request->hasFile('image')) {
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                $filename = Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension();
                $path = $request->file('image')->storeAs('images', $filename, 'public');
                $data['image'] = $path;
            }

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
