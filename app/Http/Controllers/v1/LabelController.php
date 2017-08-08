<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Label;

class LabelController extends Controller
{
    /**
     * GET /photos 获取类型列表
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
        $q = $this->request->input('q');                                        // 搜索
        $s = $this->request->input('s');                                        // 排序

        if ($videos = Label::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $videos]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * GET /video 获取视频详情
     */
    public function get($id)
    {
        if ($video = Video::repository($id)) {
            return $this->body(['data' => $video]);
        }

        return $this->error(self::NOT_FOUND);
    }
}
