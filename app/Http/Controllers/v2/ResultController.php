<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\v2\Result;
use Illuminate\Http\Request;

use App\Models\v2\ResultEvent;

class ResultController extends Controller
{
    /**
     * POST /urun.result.get
     */
    public function getWithUser(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Result::getWithUser($request->input('race_id'));
        return $this->body(['result' => $data]);
    }

}
