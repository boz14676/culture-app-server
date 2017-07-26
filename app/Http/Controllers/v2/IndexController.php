<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\Race;

class IndexController extends Controller
{
    /**
     * GET /urun.race.list
     */
    public function lists(Request $request)
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $races = Race::lists($request->input('per_page'));
    
        return $this->formatPaged($races);
    }


}
