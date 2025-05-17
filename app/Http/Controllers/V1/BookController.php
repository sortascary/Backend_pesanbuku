<?php

namespace App\Http\Controllers\V1;

use App\Models\Book;
use App\Models\BookClass;
use App\Models\BookDaerah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookResource;
use App\Http\Resources\V1\BookClassResource;
use App\Http\Resources\V1\BookOrderResource;
use App\Http\Resources\V1\BookDaerahResource;
use App\Http\Requests\V1\Book\CreateBookRequest;
use App\Http\Requests\V1\Book\CreateClassRequest;
use App\Http\Requests\V1\Book\UpdateBookStockRequest;
use App\Http\Requests\V1\Book\UpdateBookPriceRequest;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role == 'distributor') {
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

    public function orderSearch(Request $request, string $daerah)
    {
        $daerah = BookDaerah::with('book')->where('daerah',  $daerah)->get();
        return BookOrderResource::collection($daerah);
    }

    public function daerahsearch(string $place)
    {
        $bookD = BookDaerah::where('daerah', $place)->get();
        return BookDaerahResource::collection($bookD);
    }

    public function createBook(CreateBookRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try{
            $bookPost = Book::create([
                'name' => $request->name,
            ]);

            foreach($request->book_price as $book){
                BookDaerah::create([
                    'book_id' => $bookPost->id,
                    'daerah' => $book['daerah'],
                    'price' => $book['price'],
                ]);
            }
            DB::commit();

            return response()->json([
                'message' => 'Book created successfully', 
                'data' => new BookResource($bookPost)
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createClass(CreateClassRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try{
            $bookPost = BookClass::create([
                'book_id' => $request->book_id,
                'stock' => $request->stock,
                'class' => $request->class,
            ]);
            DB::commit();

            return response()->json([
                'message' => 'Stock class created successfully', 
                'data' => $bookPost
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            'message' => 'Stock updated successfully', 
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
            'message' => 'Price updated successfully', 
            'data' => new BookDaerahResource($book)
        ]);
    }

    public function deleteBook(string $id)
    {
        $book = BookDaerah::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        $book->delete();
    
        return response()->json(['message' => 'Book deleted successfully']);
    }

    public function deleteClass(string $id)
    {
        $class = BookClass::find($id);

        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $class->delete();
    
        return response()->json([
            'message' => 'Class deleted successfully',
        ]);
    }
}