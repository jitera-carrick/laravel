<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArticleService
{
    public function getFilteredArticles(array $filters)
    {
        // Validate the filters
        $validator = Validator::make($filters, [
            'title' => 'sometimes|string|max:200',
            'date' => 'sometimes|date',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $query = Article::query();

        if (isset($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (isset($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        $page = $filters['page'] ?? 1;
        $limit = min($filters['limit'] ?? 10, 50);

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
