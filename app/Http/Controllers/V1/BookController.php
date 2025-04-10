<?php

namespace App\Http\Controllers\V1;

use App\Models\Book;
use App\Models\BookClass;
use App\Models\BookDaerah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookResource;
use App\Http\Resources\V1\BookClassResource;
use App\Http\Resources\V1\BookOrderResource;
use App\Http\Resources\V1\BookDaerahResource;
use App\Http\Requests\V1\Book\UpdateBookStockRequest;
use App\Http\Requests\V1\Book\UpdateBookPriceRequest;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role == 'admin') {
            $daerah = BookDaerah::with('book')->get();
        } else {       
            $daerah = BookDaerah::with('book')->where('daerah', $user->daerah)->get();
        }

        return BookDaerahResource::collection($daerah);
    }

    public function stock()
    {
        $book = Book::with('bookdaerah', 'bookclass')->get();
        return BookResource::collection($book);
    }

    public function stocksearch(string $id)
    {
        $book = Book::with('bookdaerah', 'bookclass')->where('id', $id)->get();
        return BookResource::collection($book);
    }

    public function class()
    {
        $class = BookClass::with('book')->get();
        return BookClassResource::collection($class);
    }

    public function order(Request $request)
    {
        $user = $request->user();
        $daerah = BookDaerah::with('book')->where('daerah', $user->daerah)->get();
        return BookOrderResource::collection($daerah);
    }


    public function daerahsearch(string $place)
    {
        $bookD = BookDaerah::where('daerah', $place)->get();
        return BookDaerahResource::collection($bookD);
    }

    public function updateStock(UpdateBookStockRequest $request, string $id)
    {
        $book = BookClass::find($id);

        if (!$book) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $data = $request->validated();

        $book->update($data);
    
        return response()->json([
            'message' => 'Order updated successfully', 
            'data' => new BookClassResource($book)
        ]);
    }

    public function updatePrice(UpdateBookPriceRequest $request, string $id)
    {
        $book = BookDaerah::find($id);

        if (!$book) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $data = $request->validated();

        $book->update($data);
    
        return response()->json([
            'message' => 'Order updated successfully', 
            'data' => new BookDaerahResource($book)
        ]);
    }
}
