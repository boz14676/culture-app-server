<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\v1\Config;

class ConfigController extends Controller {

    public function index()
    {
        $data = Config::getList();
        return $this->json($data);
    }
   
}
