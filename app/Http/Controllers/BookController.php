<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookSearchRequest;
use App\Http\Requests\BookUpdateRequest;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $books = $query->with('images')
            ->latest()
            ->paginate(10);

        return BookResource::collection($books);


    }
    // public function search(BookSearchRequest $request)
    // {
    // $searchTerm = $request->get('query', '');
    // $perPage = $request->get('per_page', 10);
    // $books = Book::where('title', 'like', "%$searchTerm%")
    //     ->orWhere('description', 'like', "%$searchTerm%")
    //     ->paginate($perPage);
    // return response()->json($books);
    // } 
    public function store(Request $request)
    {
        // dd($request->all());
        $book = Book::create([
            'title' => $request->title,
            'description' => $request->description,
            'author_id' => Auth::id()
        ]);
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = [
                    'path' => $this->uploadPhoto($image, "book"),
                    'imageable_id' => $book->id,
                    'imageable_type' => Book::class,
                ];
            }
        }
        Image::insert($images);

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
        ]);

    }
    public function show(string $id)
    {
        $post = Book::findOrFail($id);
        return response()->json($post);
    }
    public function update(BookUpdateRequest $request, string $id)
    {
        // dd($request->all());
        $book = Auth::user()->books()->findOrFail($id);
        $book->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        // dd($book->images);
        if ($request->hasFile('images')) {
            foreach ($book->images as $image) {
                $this->deletePhoto($image->path);
                $image->delete();
            }
            $images = [];

            foreach ($request->file('images') as $image) {
                $images[] = [
                    'path' => $this->uploadPhoto($image, 'book'),
                    'imageable_id' => $book->id,
                    'imageable_type' => Book::class,
                ];
            }
            Image::insert($images);
        }

        return response()->json([
            'success' => true,
            'book' => new BookResource($book->load('images')),
        ]);

    }
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
        foreach ($book->images as $image) {
            $this->deletePhoto($image->path);
            $image->delete();
        }
        $book->delete();
    }
}


