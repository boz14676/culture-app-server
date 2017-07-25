<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

use App\Models\v2\Photo;

class PhotoController extends Controller
{
    /**
     * POST /urun.photo.lists
     */
    public function lists(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        $photos = Photo::repositoryWithList($request->input('race_id'), $request->input('per_page'));
        
        return $this->formatPaged($photos);
    }
    
    /**
     * POST /urun.photo.upload
     */
    public function uploads(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
            'photo' => 'required|image',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        $photo = $request->file('photo');
        
        if (Photo::uploads($request->race_id, $photo)) {
            return $this->body();
        }
        
        return $this->error(self::BAD_REQUEST, Photo::errorMsg());
        
    }
}
