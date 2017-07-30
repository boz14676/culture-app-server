<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Comment;

class CommentController extends Controller
{
    /**
     * GET /comments 获取评论(s)
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            'q' => 'array',
            's' => 'array',
            'q.commentable_type' => 'required|string',
            'q.commentable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 搜索
        $s = $this->request->input('s');                                        // 排序

        if ($comment = Comment::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $comment]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

}
