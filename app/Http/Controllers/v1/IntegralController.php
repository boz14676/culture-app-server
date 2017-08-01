<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\UserIntegral;
use App\Models\v1\IntegralTask;

class IntegralController extends Controller
{
    /**
     * GET /integral_tasks 获取积分任务(s)
     */
    public function getIntegralTasks()
    {
        if ($integral_tasks = IntegralTask::repositories(0)) {
            return $this->body(['data' => $integral_tasks]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * GET /user/integrals 获取用户积分记录(s)
     */
    public function getIntegrals()
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

        if ($integrals = UserIntegral::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $integrals]);
        }

        return $this->error(self::BAD_REQUEST, UserIntegral::errorMsg());
    }

}
