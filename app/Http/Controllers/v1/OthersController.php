<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Area;
use App\Models\v1\HomeSection;
use App\Models\v1\Hotsearch;

class OthersController extends Controller
{
    /**
     * GET /hotsearches 获取热搜(s)
     */
    public function getHotsearches()
    {
        $rules = [
            'q' => 'array',
            'q.article_category_id' => 'required|integer|min:1',
            'numbers' => 'integer|min:1'
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $numbers = $this->request->input('numbers', 6);                        // 显示数量

        if ($hotsearch = Hotsearch::repositories($numbers)) {
            return $this->body(['data' => $hotsearch]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * GET /home_sections 获取首页推荐栏目
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

        if ($home_sections = HomeSection::_repositories($confs)) {
            return $this->body(['data' => $home_sections]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * GET /areas 获取区域(s)
     */
    public function getAreas()
    {
        $rules = [
            's' => 'array',
            'q' => 'array',
            'q.parent_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $q = $this->request->input('q');    // 筛选
        $s = $this->request->input('s');    // 排序

        if ($areas = Area::repositories(0, $q, $s)) {
            return $this->body(['data' => $areas]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
