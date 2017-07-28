<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\ArticleCategory;

class ArticleController extends Controller
{
    /**
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
     * GET /articles
     * 获取文章(s)
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

        $attribute = [
            'id' => 1,
            'name' => '文章测试',
            'thumbnail' => 'http://v1.qzone.cc/avatar/201508/17/09/21/55d1372b820a3621.jpg%21200x200.jpg',
            'banner' => 'http://v1.qzone.cc/avatar/201508/17/09/21/55d1372b820a3621.jpg%21200x200.jpg',
            'item' => ['测试标签1', '测试标签2'],
            'distance' => '1223',
            'location' => '1223',
            'desc' => '描述测试',
            'timed_at' => '2017-07-27 00:00:00',
            'activity_nums' => '12',
        ];
        for ($i=1; $i<=10; $i++) {
            $attribute['id'] = $i;
            $articles[] = $attribute;
        }

        return $this->test_formatPaged(collect($articles));
    }
    

}
