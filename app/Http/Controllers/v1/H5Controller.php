<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\Video;

class H5Controller extends Controller
{
    /**
     * GET /urun.test
     */
    public function test(Request $request)
    {
        Video::pushToUser();
    }
}
