<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;
use App\Models\Article;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'images' => 'array',
            'images.*' => 'mimes:png,jpeg,jpg|max:8000'
        ]);

        $userId = $request->user()->id;

        DB::unprepared("
            SET search_path to mitienda{$userId};
        ");

        $article = Article::create([
            'title' => $request['title'],
            'category_id' => $request['category'],
        ]);

        $images = $request->file('images');

        foreach ($images as $image) {
            //the upload method handles the uploading of the file and can accept attributes to define what should happen to the image

            //Also note you could set a default height for all the images and Cloudinary does a good job of handling and rendering the image.
            Cloudder::upload($image->getRealPath(), null, array(
                "folder" => $userId,  "overwrite" => FALSE,
                "resource_type" => "image"
            ));

            $publicId = Cloudder::getPublicId();

            Image::create([
                'url' => Cloudder::secureShow($publicId),
                'article_id' => $article->id,
            ]);
        }

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function get(Request $request)
    {
        DB::unprepared("
            SET search_path to mitienda{$request->user()->id};
        ");

        $articles = Article::with('images')->get();

        return response()->json($articles);
    }
}
