<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\v2\Video;

use Log;
class VideoController extends Controller
{
    /**
     * POST /urun.video.create
     */
    public function createVideo(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
            'photos'  => 'required|array',
            // 'form_id' => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        if ($photos = Video::createVideo($request->input('race_id'), $request->input('photos'), $request->input('form_id')) ) {
            return $this->body();
        }
        return $this->error(self::BAD_REQUEST, Video::errorMsg());
    }
    
    /**
     * POST /service/urun.video.receive
     */
    public function receiveVideo(Request $request)
    {
        $rules = [
            'task_id' => 'required|string',
            'task_status' => 'required|integer',
            'export_video' => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        if ($photos = Video::receiveVideo($this->validated)) {
            return $this->body();
        }
        
        return $this->error(self::BAD_REQUEST, Video::errorMsg());
    }
    
    /**
     * POST /urun.video.delete
     */
    public function deleteVideos(Request $request)
    {
        $rules = [
            'videos' => 'required|array'
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        if ($videos = Video::deleteVideos($request->input('videos'))) {
            return $this->body();
        }
        
        return $this->error(self::NOT_FOUND);
    }
    
    /**
     * POST /urun.video.lists
     */
    public function listsWithUser(Request $request)
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        if ($photos = Video::listsWithUser($request->input('per_page'))) {
            return $this->formatPaged($photos);
        }
        
        return $this->error(self::NOT_FOUND);
    }
    
}
