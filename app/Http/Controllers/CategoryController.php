<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        DB::unprepared("
            SET search_path to mitienda{$request->user()->id};
        ");

        Category::create([
            'title' => $validatedData['title'],
        ]);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function get(Request $request)
    {
        DB::unprepared("
            SET search_path to mitienda{$request->user()->id};
        ");

        $categories = Category::all();

        return response()->json($categories);
    }
}
