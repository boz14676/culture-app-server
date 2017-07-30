<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Photo;

class PhotoController extends Controller
{
    /**
     * GET /photos 获取图片(s)
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            'q' => 'array',
            's' => 'array',
            'q.imageable_type' => 'required|string',
            'q.imageable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 搜索
        $s = $this->request->input('s');                                        // 排序

        if ($article_categories = Photo::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $article_categories]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
