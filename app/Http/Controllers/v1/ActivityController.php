<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Activity;

class ActivityController extends Controller
{
    /**
     * GET /activities 获取活动(s)
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            'q' => 'array',
            's' => 'array',
            'q.activitiable_type' => 'required|string',
            'q.activitiable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 搜索
        $s = $this->request->input('s');                                        // 排序

        if ($activities = Activity::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $activities]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * GET /activity 获取活动
     */
    public function get($id=0)
    {
        if ($activity = Activity::find($id)) {
            return $this->body(['data' => $activity->makeVisible(['details'])]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
