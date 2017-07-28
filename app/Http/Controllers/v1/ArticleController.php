<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Article;
use App\Models\v1\ArticleCategory;

class ArticleController extends Controller
{
    /**
     * 获取文章类别(s)
     * GET /article_categories
     */
    public function categories()
    {
        $rules = [
            'topid'      => 'required|integer|min:0',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $topid = $this->request->input('topid');

        if ($article_categories = ArticleCategory::repositories($topid)) {
            return $this->body(['data' => $article_categories]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }


    /**
     * 获取文章(s)
     * GET /articles
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            'article_category_id' => 'integer|min:1',
            's' => 'array',
            'q' => 'array',
            's.*' => 'string',
            'q.*' => 'string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $article_category_id = $this->request->input('article_category_id', 0);    // 文章分类ID
        $s = $this->request->input('s');                                        // 排序
        $q = $this->request->input('q');                                        // 筛选

        if ($articles = Article::repositories($article_category_id, $per_page, $s, $q)) {
            return $this->formatPaged(['data' => $articles]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
    
    /**
     * 获取文章
     * GET /article
     */
    public function get($id=0)
    {
        if ($article = Article::repository($id)) {
            return $this->body(['data' => $article]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
