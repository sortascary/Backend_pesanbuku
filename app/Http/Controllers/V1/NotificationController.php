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

        $notification = Notification::where('user_id', $user->id);
        return $notification;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateNotificationRequest $request)
    {
        $validated = $request->validated();

        $notification = Notification::create([
            'message' => $validated['message'],
            'sub_message' => $validated['sub_message'],
            'user_id' => $validated['user_id'],
        ]);

        return response()->json([
            'message' => 'Notification created successfully',
            'data' => $notification
        ], 201);
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
