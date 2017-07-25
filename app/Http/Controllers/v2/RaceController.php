<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\Race;

class RaceController extends Controller
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
    
    /**
     * GET /urun.race.get
     */
    public function get(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $race = Race::get($request->input('id'));
    
        return $this->body(['race' => $race]);
    }



}
