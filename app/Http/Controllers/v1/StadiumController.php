<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Stadium;

class StadiumController extends Controller
{

    /**
     * GET /stadiums 获取场馆(s)
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

        if ($stadiums = Stadium::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $stadiums]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
    
    /**
     * GET /article 获取场馆
     */
    public function get($id=0)
    {
        if ($stadium = Stadium::repository($id)) {
            return $this->body(['data' => $stadium]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}
