<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\HomeSection;
use App\Models\v1\Hotsearch;

class OthersController extends Controller
{
    /**
     * 获取热搜(s)
     * GET /hotsearches
     */
    public function getHotsearches()
    {
        $rules = [
            'article_category_id' => 'required|integer|min:1',
            'numbers' => 'integer|min:1'
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $article_category_id = $this->request->input('article_category_id'); // 文章分类对象 ID
        $numbers = $this->request->input('numbers', 6);                        // 显示数量

        if ($hotsearch = Hotsearch::repositories($article_category_id, $numbers)) {
            return $this->body(['data' => $hotsearch]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * 获取首页推荐栏目
     * GET home_sections
     */
    public function getHomeSections()
    {
        $rules = [
            'confs' => 'required|array',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $confs = $this->request->input('confs'); // 配置信息

        if ($home_sections = HomeSection::repositories($confs)) {
            return $this->body(['data' => $home_sections]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
