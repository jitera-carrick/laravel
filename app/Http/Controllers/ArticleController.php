<?php

namespace App\Http\Controllers;

use App\Models\Article; // Assuming Article model exists
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    /**
     * Handle the GET request for filtering/searching articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'date' => 'sometimes|date_format:Y-m-d',
            'page' => 'sometimes|numeric|min:1',
            'limit' => 'sometimes|numeric|between:1,100'
        ], [
            'title.max' => 'You cannot input more than 200 characters.',
            'date.date_format' => 'Wrong date format.',
            'page.numeric' => 'Wrong format.',
            'page.min' => 'Page must be greater than 0.',
            'limit.numeric' => 'Wrong format.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $query = Article::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $limit = $request->limit ?? 10; // Default to 10 if no limit is provided
        $articles = $query->paginate($limit)->withQueryString();

        return response()->json([
            'status' => 200,
            'articles' => $articles->items(),
            'total_pages' => $articles->lastPage(),
            'limit' => $limit,
            'page' => $articles->currentPage()
        ]);
    }

    // Method to update an article
    public function updateArticle(Request $request, $id): JsonResponse
    {
        // Validate the "id" parameter to ensure it is a number
        if (!is_numeric($id)) {
            return response()->json(['message' => 'Wrong format.'], 422);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'This article is not found.'], 404);
        }

        $user = Auth::user();
        if ($user->id !== $article->author_id && !$user->hasRole('writer')) {
            return response()->json(['message' => 'You do not have permission to update this article.'], 403);
        }

        $article->update($request->only(['title', 'content']));

        return response()->json([
            'status' => 200,
            'article' => $article
        ]);
    }

    // ... other methods ...
}
