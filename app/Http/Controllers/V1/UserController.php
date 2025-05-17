<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
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

    public function getuserdata(Request $request){
        $user = $request->user();

        return response()->json([
            'data' => new UserResource($user)
        ]);
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
            'email' => $data['email'],
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

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $data['email'])->first();

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

    //TODO: update this this & test it
    public function sendResetToken(Request $request)
    {
        $user = $request->user();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );
        
        $fcmToken = $user->fcm_token;

        if ($fcmToken) {
            $messaging = Firebase::messaging();

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create('Reset Password', 'Your reset token is: ' . $token));

            $messaging->send($message);
        }

        return back()->with('status', 'Reset token sent via FCM (and shown here for test): ' . $token);
    }

    //TODO: update this this & test it
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Invalid or expired token.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(60);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password reset successful!');
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
