<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Article;
use App\Models\v1\ArticleCategory;

class ArticleController extends Controller
{
    /**
     * GET /article_categories 获取文章类别(s)
     */
    public function categories()
    {
        $rules = [
            'number'      => 'integer|min:1',
            'q.topid'     => 'integer|min:0',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $q = $this->request->input('q');                      // 筛选
        $s = $this->request->input('s');                      // 排序
        $numbers = $this->request->input('numbers', 0);       // 显示数量

        if ($article_categories = ArticleCategory::repositories($numbers, $q, $s)) {
            return $this->body(['data' => $article_categories]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }


    /**
     * GET /articles 获取文章(s)
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 筛选
        $s = $this->request->input('s');                                        // 排序

        if ($articles = Article::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $articles]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
    
    /**
     * GET /article 获取文章
     */
    public function get($id=0)
    {
        if ($article = Article::repository($id)) {
            return $this->body(['data' => $article]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
