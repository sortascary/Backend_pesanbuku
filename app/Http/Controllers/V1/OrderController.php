<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Models\Order;
use App\Models\Book;
use App\Models\OrderBook;
use App\Models\BookClass;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\OrderBookResource;
use App\Http\Resources\V1\LaporanResource;
use App\Http\Requests\V1\Order\OrderRequest;
use App\Http\Requests\V1\Order\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Jobs\SendOrderReminderNotification;
use App\Notifications\SendCustomLink;
use Illuminate\Support\Facades\Notification;


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
        $user = $request->user();

        $TotalBook = Book::count();
        $TotalStock = BookClass::sum('stock');
        $ORDPesan = Order::where('status', 'dipesan')->count();
        $ORDProses = Order::where('status', 'diproses')->count();
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $tagihanQuery = Order::where(function ($query) use ($sixMonthsAgo) {
            $query->where('status', '!=', 'done')
                ->orWhere(function ($q) use ($sixMonthsAgo) {
                    $q->where('payment', 'angsuran')
                        ->where('status', 'done')
                        ->where('done_at', '>=', $sixMonthsAgo);
                });
        });

        if ($user->role === 'distributor') {
            // no restriction
        } elseif ($user->role === 'sekolah') {
            $tagihanQuery->where('user_id', $user->id);
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $TotalTagihan = $tagihanQuery->count();



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
                'unpaid_amount' => 0,
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

            $orderPost->update([
                'total_book_price' => $totalPrice,
                'unpaid_amount'    => $totalPrice,
            ]);

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

    public function test()
    {
        try {
            $pdf = PDF::loadView('pdf.test');
            $filename = 'what.pdf';
            return $pdf->download($filename);
            // return response($pdf->output(), 200)
            //     ->header('Content-Type', 'application/pdf')
            //     ->header('Content-Disposition', 'inline; filename="test_file.pdf"');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

            if (isset($data['paid_amount'])) {
                $order->unpaid_amount = max(0, $order->unpaid_amount - $data['paid_amount']);
            }

            // If status changes to 'done', set done_at & schedule reminders
            if ($order->status != 'done' && $data['status'] == 'done') {
                $data['done_at'] = now();

                if ($order->payment === 'angsuran') {
                    for ($i = 1; $i <= 6; $i++) {
                        SendOrderReminderNotification::dispatch($order, $i)
                            ->delay(now()->addMonths($i));
                    }
                }
            } else {
                $data['done_at'] = null;
            }

            $order->fill($data);
            $order->save();

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
    public function destroy(Request $request, string $id)
    {
        $messageData = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $reason = $messageData['message'];

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

        if ($order->user) {
            $reason = $message;
            $order->user->notify(new OrderCancledNotification($order, $reason));
        }

        if ($order->status != 'done'){
            $order->delete();
        }else{
            $order->forceDelete();
        }


        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function laporan(Request $request, $startDate, $endDate)
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 422);
        }

        $user = $request->user();

        $orderquery = Order::withTrashed()
            ->with(['user', 'orderbook'])
            ->where('status', 'done')
            ->whereBetween('done_at', [$start, $end])
            ->orderBy('done_at', 'desc');

        $earnings = (clone $orderquery)->get()->sum('total_book_price');

        $total_order = (clone $orderquery)->count();
        $orders = $orderquery->paginate(10);

        return new LaporanResource([
            'orders' => $orders,
            'total_order' => $total_order,
            'total_penjualan' => $earnings,
        ]);
    }

    public function laporandelete(Request $request, $startDate, $endDate)
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 422);
        }

        $user = $request->user();

        $orderquery = Order::withTrashed()
            ->with(['user', 'orderbook'])
            ->where('status', 'done')
            ->whereBetween('done_at', [$start, $end])
            ->orderBy('done_at', 'desc')
            ->get();

        $orderquery->each->forceDelete();
        
        return response()->json([
            'message' => 'Orders permanently deleted',
            'deleted_count' => $deletedCount
        ]);
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
                                ->where('done_at', '<', $sixMonthsAgo);
                        });
                });
        } else {
            $ordersQuery->where(function ($query) use ($sixMonthsAgo) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'done');
                })->orWhere(function ($q) use ($sixMonthsAgo) {
                    $q->where('payment', 'angsuran')
                    ->where('status', 'done')
                    ->where('done_at', '>=', $sixMonthsAgo);
                });
            });
        }

        if ($user->role === 'distributor') {
            // no restriction
        } elseif ($user->role === 'sekolah') {
            $ordersQuery->where('user_id', $user->id);
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $orders = $ordersQuery->paginate(10);

        return OrderResource::collection($orders);
    }

    public function emailpdf(Request $request, string $id){
        $user = $request->user();

        $link = url('api/order/createpdf/'. $id);

        $user->notify(new SendCustomLink($link));

        return response()->json(['message' => 'Email sent']);
    }

    public function generatePDF(String $id)
    {
        $order = Order::with(['orderbook', 'orderbook.bookClass.book.bookdaerah'])->findOrFail($id);

        $allBooks = BookClass::with(['book.bookdaerah'])->get();

        // Group order items by bookClass ID for quick lookup
        $ordered = $order->orderbook->keyBy('book_class_id');

        $groupedBooks = [];

        $doneAt = Carbon::parse($order->done_at);
        $year = $doneAt->year;
        $month = $doneAt->month;

        $doneAtFormatted = $doneAt->locale('id')->translatedFormat('d F Y');

        $semester = $month >= 7
            ? "Semester ganjil {$year}/" . ($year + 1)
            : "Semester genap " . ($year - 1) . "/{$year}";

        foreach ($allBooks as $bookClass) {
            $book = $bookClass->book;
            $kelas = $bookClass->class;
            $title = $book->name;

            $price = optional($book->bookdaerah->firstWhere('daerah', $order->daerah))->price ?? 0;

            if (!isset($groupedBooks[$title])) {
                $groupedBooks[$title] = [
                    'title' => $title,
                    'price' => $price,
                    'quantities' => array_fill(1, 6, 0),
                    'total_ambil' => 0,
                ];
            }

            $qty = 0;

            if ($ordered->has($bookClass->id)) {
                $qty = $ordered[$bookClass->id]->amount;
            }

            if ($kelas >= 1 && $kelas <= 6) {
                $groupedBooks[$title]['quantities'][$kelas] += $qty;
                $groupedBooks[$title]['total_ambil'] += $qty;
            }
        }

        // Format for PDF
        $items = [];
        $no = 1;
        $total_pesanan = 0;

        foreach ($groupedBooks as $group) {
            $total_bayar = $group['total_ambil'] * $group['price'];
            $total_pesanan += $total_bayar;

            $items[] = [
                'no' => $no++,
                'title' => $group['title'],
                'price' => $group['price'],
                'quantities' => array_values($group['quantities']),
                'total_ambil' => $group['total_ambil'],
                'total_bayar' => $total_bayar,
            ];
        }

        // ✅ Add order_books with NULL book_class_id as separate rows
        $nullBooks = $order->orderbook->whereNull('book_class_id');

        foreach ($nullBooks as $nullBook) {
            $items[] = [
                'no' => $no++,
                'title' => $nullBook->name ?? '(Tanpa Nama)',
                'price' => $nullBook->bought_price ?? 0,
                'quantities' => array_fill(0, 6, ''), // Blank for class breakdown
                'total_ambil' => $nullBook->amount,
                'total_bayar' => $nullBook->subtotal,
            ];
            $total_pesanan += $nullBook->subtotal ?? 0;
        }

        $data = [
            'school' => $order->schoolName,
            'done_at' => $doneAtFormatted,
            'semester' => $semester,
            'city' => $order->daerah,
            'items' => $items,
            'total_pesanan' => $total_pesanan,
        ];

        try {
            $pdf = PDF::loadView('pdf.pdf_note', $data);
            return $pdf->download("pesanan_" . $order->schoolName . "_" . $order->id . ".pdf");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}