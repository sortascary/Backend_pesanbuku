<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Notification;
use App\Models\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->unreadNotifications()->latest()->paginate(10);
        $order = Order::with(['user', 'orderbook'])->find($notification->data['order_id'] ?? null);

        return response()->json(
            $notifications->map(function ($notification) {
                $orderId = $notification->data['order_id'] ?? null;

                $order = null;
                if ($orderId) {
                    $order = Order::with(['user', 'orderbook'])->withTrashed()->find($orderId);
                }
                
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'order' => $order ? new OrderResource($order) : null,
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

        $notification->Delete();

        return response()->json(['message' => 'Notification marked as read'], 201);
    }

    public function ReadAll(Request $request)
    {
        $request->user()->unreadNotifications->each->Delete();

        return response()->json(['message' => 'All notifications marked as read'], 201);
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
