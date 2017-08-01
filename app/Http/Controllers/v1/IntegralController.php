<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Integral;
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
}
