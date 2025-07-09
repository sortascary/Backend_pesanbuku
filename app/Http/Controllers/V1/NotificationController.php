<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Requests\V1\Notification\CreateNotificationRequest;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->get();

        return response()->json(
            $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'order_id' => $notification->data['order_id'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            })
        );
    }

    public function Read(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->unreadNotifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function ReadAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        } else{            
            $notification = Notification::where('user_id', $id)->get();
        }
        

        return $notification;
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
