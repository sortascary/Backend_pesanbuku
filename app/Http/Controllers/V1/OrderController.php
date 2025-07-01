<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderBook;
use App\Models\BookClass;

use App\Models\Book;

use Illuminate\Http\Request;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\OrderBookResource;
use App\Http\Resources\V1\LaporanResource;
use App\Http\Requests\V1\Order\OrderRequest;
use App\Http\Requests\V1\Order\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role == 'distributor') {
            $query = Order::with(['user', 'orderbook'])
            ->orderByDesc('created_at');
        } else {       
            $query = Order::with(['user', 'orderbook'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');
        }

        $orders = $query->paginate(20);

        return OrderResource::collection($orders);
    }

    public function init(Request $request)
    {
        $TotalBook = Book::count();
        $TotalStock = BookClass::sum('stock');
        $ORDPesan = Order::where('status', 'dipesan')->count();
        $ORDProses = Order::where('status', 'diproses')->count();
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $ordersQuery->where(function ($query) use ($sixMonthsAgo) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'done');
                })->orWhere(function ($q) use ($sixMonthsAgo) {
                    $q->where('payment', 'angsuran')
                    ->where('status', 'done')
                    ->where('updated_at', '>=', $sixMonthsAgo);
                });
            });

        $TotalTagihan = Order::where(function ($query) use ($sixMonthsAgo) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'done');
                })->orWhere(function ($q) use ($sixMonthsAgo) {
                    $q->where('payment', 'angsuran')
                    ->where('status', 'done')
                    ->where('updated_at', '>=', $sixMonthsAgo);
                });
            })->count();



        return response()->json([
                'book' => $TotalBook,
                'stock' => $TotalStock,
                'dipesan' => $ORDPesan,
                'diproses' => $ORDProses,
                'tagihan' => $TotalTagihan,
            ], 200);
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
                'user_id' => $user->role == "sekolah"? $user->id : null,
                'phone' => $request->phone ?? $user?->phone,
                'schoolName' => $request->schoolName ?? $user?->schoolName,
                'daerah' => $request->daerah ?? $user?->daerah,
                'payment' => $request->payment,
                'status' => $request->status,
                'total_book_price' => 0,
            ]);

            $totalPrice = 0;

            foreach ($request->books as $book) {
                $bookClass = BookClass::with('book.bookdaerah')->findOrFail($book['book_class_id']);

                $bookDaerah = $bookClass->book->bookdaerah->where('daerah', $request->daerah ?? $user?->daerah)->first();
                if (!$bookDaerah) {
                    DB::rollback();
                    return response()->json([
                        'error' => "Book price not found for daerah: " . ($request->daerah ?? $user?->daerah ?? 'unknown')
                    ], 400);
                }

                if ($request->status == 'diproses') {
                    if ($bookClass->stock < $book['amount']) {
                        DB::rollback();
                        return response()->json([
                            'error' => "Insufficient stock for book_class_id: " . $book['book_class_id']
                        ], 400);
                    }

                    $bookClass->stock -= $book['amount'];
                    $bookClass->save();
                }

                $boughtPrice = $bookDaerah->price ?? 0;
                $subtotal = $boughtPrice * $book['amount'];
                $totalPrice += $subtotal;

                OrderBook::create([
                    'order_id' => $orderPost->id,
                    'book_class_id' => $book['book_class_id'],
                    'name' => $bookClass->book->name,
                    'amount' => $book['amount'],
                    'bought_price' => $boughtPrice,
                    'subtotal' => $subtotal,
                ]);
            }


            $orderPost->update(['total_book_price' => $totalPrice]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully', 
                'data' => $orderPost
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage(),
                'debug' => isset($bookClass) ? ($bookClass->book->name ?? 'N/A') : 'Book class not set',
            ], 500);
        }

    }

    public function search(Request $request, string $status)
    {
        $user = $request->user();

        if ($user->role == 'distributor') {
            $orders = Order::with(['user', 'orderbook'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(10);
        } else {       
            $orders = Order::with(['user', 'orderbook'])
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(10);
        }

        return OrderResource::collection($orders);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateorder(UpdateOrderRequest $request, string $id)
    {
        $order = Order::with('orderbook.bookclass')->find($id);
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {
            // If status is changing to 'diproses', deduct stock
            if ($order->status !== 'diproses' && $data['status'] == 'diproses') {
                foreach ($order->orderbook as $orderBook) {
                    $bookClass = $orderBook->bookclass;

                    if ($bookClass->stock < $orderBook->amount) {
                        DB::rollBack();
                        return response()->json([
                            'error' => "Insufficient stock for book_class_id: " . $bookClass->id
                        ], 400);
                    }

                    $bookClass->stock -= $orderBook->amount;
                    $bookClass->save();
                }
            }

            $order->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            'data' => new OrderBookResource($order)
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

        if ($order->status == 'diproses') {
            $orderBooks = OrderBook::with('bookclass')->where('order_id', $id)->get();
    
            foreach ($orderBooks as $orderBook) {
                $bookClass = $orderBook->bookclass;
                if ($bookClass) {
                    $bookClass->stock += $orderBook->amount;
                    $bookClass->save();
                }
            }
        }
    
        $order->delete();
    
        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function laporan(Request $request){

        $user = $request->user();

        $orders = Order::with(['user', 'orderbook'])
        ->where('status', 'done')
        ->orderBy('created_at', 'desc')
        ->get();

        return new LaporanResource($orders);
    }

    public function tagihan(Request $request, string $isPayed)
    {
        $user = $request->user();

        $isPayedBool = filter_var($isPayed, FILTER_VALIDATE_BOOLEAN);
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $ordersQuery = Order::with(['user', 'orderbook']);

        if ($isPayedBool) {
            $ordersQuery->where('status', 'done')
                ->where(function ($query) use ($sixMonthsAgo) {
                    $query->where('payment', '!=', 'angsuran')
                        ->orWhere(function ($q) use ($sixMonthsAgo) {
                            $q->where('payment', 'angsuran')
                                ->where('updated_at', '<', $sixMonthsAgo);
                        });
                });
        } else {
            $ordersQuery->where(function ($query) use ($sixMonthsAgo) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'done');
                })->orWhere(function ($q) use ($sixMonthsAgo) {
                    $q->where('payment', 'angsuran')
                    ->where('status', 'done')
                    ->where('updated_at', '>=', $sixMonthsAgo);
                });
            });
        }

        //Role filtering
        if ($user->role === 'distributor') {
            // no restriction
        } elseif ($user->role === 'sekolah') {
            $ordersQuery->where('user_id', $user->id);
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $orders = $ordersQuery->get();

        return OrderResource::collection($orders);
    }


}
