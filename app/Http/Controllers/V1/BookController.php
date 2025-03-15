<?php

namespace App\Http\Controllers\V1;

use App\Models\Book;
use App\Models\BookClass;
use App\Models\BookDaerah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookResource;
use App\Http\Resources\V1\BookClassResource;
use App\Http\Resources\V1\BookDaerahResource;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $book = Book::with('bookdaerah', 'bookclass')->get();
        return BookResource::collection($book);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function class()
    {
        $class = BookClass::with('book')->get();
        return BookClassResource::collection($class);
    }

    public function daerah()
    {
        $daerah = BookDaerah::with('book')->get();
        return BookDaerahResource::collection($daerah);
    }

    public function daerahsearch(string $Daerah)
    {
        $bookD = BookDaerah::where('daerah', $Daerah)->get();
        return BookDaerahResource::collection($bookD);
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
        //
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
