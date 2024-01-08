<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterArticlesRequest;
use App\Services\ArticleService;
use App\Http\Resources\ArticleCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function filterArticles(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'date' => 'sometimes|date',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer',
        ], [
            'title.max' => 'You cannot input more than 200 characters.',
            'date.date' => 'Wrong date format.',
            'page.integer' => 'Page must be a number.',
            'limit.integer' => 'Limit must be a number.',
            'page.min' => 'Page must be greater than 0.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $articles = $this->articleService->getFilteredArticles($validator->validated());
            return response()->json(new ArticleCollection($articles));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
