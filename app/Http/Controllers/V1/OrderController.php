<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderBook;
use App\Models\BookClass;

use Illuminate\Http\Request;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\OrderBookResource;
use App\Http\Requests\V1\OrderRequest;
use App\Http\Requests\V1\Order\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $order = Order::with('user', 'orderbook')->get();
        return OrderResource::collection($order);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $user = $request->user();

            $orderPost = Order::create([
                'user_id' => $user->id,
                'phone' => $request->phone ?? $user?->phone,
                'schoolName' => $request->schoolName ?? $user?->schoolName,
                'daerah' => $request->daerah ?? $user?->daerah,
                'payment' => $request->payment,
                'isPayed' => $request->isPayed ?? false,
                'status' => $request->status,
                'total_book_price' => 0,
            ]);

            $totalPrice = 0;

            foreach ($request->books as $book) {
                $bookClass = BookClass::findOrFail($book['book_class_id']);

                // Fix bookdaerah lookup
                $bookDaerah = $bookClass->book->bookdaerah->where('daerah', $user?->daerah)->first();
                if (!$bookDaerah) {
                    DB::rollback();
                    return response()->json([
                        'error' => "Book price not found for daerah: " . ($user->daerah ?? 'unknown')
                    ], 400);
                }

                $subtotal = $bookDaerah->price * $book['amount'];
                $totalPrice += $subtotal;

                OrderBook::create([
                    'order_id' => $orderPost->id,
                    'book_class_id' => $book['book_class_id'],
                    'amount' => $book['amount'],
                    'subtotal' => $subtotal,
                ]);
            }

            $orderPost->update(['total_book_price' => $totalPrice]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully', 
                'order' => $orderPost
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function search(Request $request, string $status)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = Order::with(['user', 'orderbook'])
        ->where('user_id', $user->id)
        ->where('status', $status)
        ->get();

        return OrderResource::collection($orders);
    }

    public function searchAdmin(Request $request, string $status)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = Order::with(['user', 'orderbook'])
        ->where('status', $status)
        ->get();

        return OrderResource::collection($orders);
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
    public function updateorder(UpdateOrderRequest $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $data = $request->validated();

        $order->update($data);
    
        return response()->json([
            'message' => 'Order updated successfully', 
            'order' => new OrderResource($order)
        ]);
    }

    public function updatebook(Request $request, string $id)
    {
        $order = OrderBook::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        $data = $request->validated();
    
        $order->update($data);
    
        return response()->json([
            'message' => 'Order updated successfully', 
            'order' => new OrderBookResource($order)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        OrderBook::where('order_id', $id)->delete();
    
        $order->delete();
    
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
