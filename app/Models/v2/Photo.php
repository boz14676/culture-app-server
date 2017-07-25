<?php

namespace App\Models\v2;

use App\Models\BaseModel;
use Image;

class Photo extends BaseModel
{
    const ISFROM_WS = 1;
    const ISFROM_USER = 2;
    
    protected $table = 'photos';
    
    protected $fillable = ['isfrom', 'race_id', 'position', 'result', 'filename'];
    protected $visible = ['id', 'isfrom', 'result_time', 'full_url', 'created_at'];
    protected $appends = ['result_time','full_url'];
    
    // 当前图片是否属于维赛推送
    public function isWs()
    {
        return $this->isfrom == self::ISFROM_WS;
    }
    
    /**
     * 【普通】写入图片模型对象
     */
    public static function uploads($race_id, $photo) {
        if (!$user = User::using($race_id)) {
            self::errorMsg(trans('message.user.cannot_found'));
            return false;
        }
    
        if ($photo->isValid()) {
            $filename = time() . rand(111,999) . '.' . $photo->getClientOriginalExtension();
            
            $img = Image::make($photo);
            $img_ratio = round($img->width() / $img->height(), 2);
            $expect_width = 1000;
            $img->resize($expect_width, $expect_width / $img_ratio);
            $img->save(app()->basePath().'/public/file/photos/user/'.$filename);
        } else {
            self::errorMsg(trans('message.error.upload_failed'));
        
            return false;
        }
        
        // 写入
        $photo = self::create(['filename' => $filename, 'race_id' => $race_id, 'isfrom' => self::ISFROM_USER]);
        
        $user->photosWithRace()->attach($photo->id, ['race_id' => $race_id]);
        
        return true;
    }
    
    /**
     * 【维赛】写入图片模型对象
     */
    public static function uploadsWs($file)
    {
        $filename = $file->getClientOriginalName(); // 照片名称

        $file_info = self::ansPhoto($filename);
    
        if ($file->isValid()) {
            $file->move(app()->basePath().'/public/file/photos/ws', $filename);
        } else {
            self::errorMsg(trans('message.error.upload_failed'));
            
            return false;
        }

        // 写入图片模型对象
        if (self::create(array_merge($file_info, ['filename' => $filename, 'isfrom' => self::ISFROM_WS])) ) {
            return true;
        }
        return false;
    }

    public function getFullUrlAttribute()
    {
        $uri = $this->isfrom == self::ISFROM_USER ? 'file/photos/user/' : 'file/photos/ws/';
        
        return env('storage_api_host'). $uri .$this->filename;
    }
    
    // 获取照片时间
    public function getResultTimeAttribute()
    {
        $time = $this->isWs() ? $this->attributes['result'] : strtotime($this->attributes['created_at']);
        return date('Y-n-j G:i:s', $time);
    }
    
    // repositoryWithList
    public static function repositoryWithList($race_id, $per_page)
    {
        $user = User::using($race_id);
        if ($race = Race::find($race_id)) {
            // 维赛计时赛事
            if ($race->isWs()) {
                // 如果当前用户在当前比赛中没有匹配照片
                if (!$user->hasWsPhotos()) {
                    // 比赛已经结束
                    if($race->hasDone()) {
                        // 获取用户站点成绩 匹配出来的所有图片
                        if (!$user->getResultEvents()->isEmpty()) {
                            $photo_ids = $user->getResultEvents()->map(function ($resultEvent) {
                                return $resultEvent->getPhotos()->map(function ($photo) {
                                    return $photo->id;
                                });
                            });
                            // 将用户和图片关联
                            $user->photosWithRace()->attach($photo_ids->collapse()->toArray(), ['race_id' => $race_id]);
                        } else {
                            return false;
                        }
                    }
                }
            }
    
            // 照片
            $photos = $user->photosWithRace()
                ->when($race->isWs(), function($query) {
                    $query->orderBy('isfrom', 'desc');
                    $query->orderBy('result', 'desc');
                    $query->orderBy('created_at', 'desc');
                    
                    return $query;
                })
                ->when(!$race->isWs(), function($query) {
                    $query->orderBy('created_at', 'desc');
                    
                    return $query;
                })
                ->simplePaginate($per_page);
            
            // dd($photos);
            
            return $photos;
        }
        
        return false;
    }

    public static function ansPhoto($filename)
    {
        $file_full = explode('.', $filename);
        $file_full_base = explode('_', $file_full[0]);
        $race_id = $file_full_base[0];
        $position = $file_full_base[1];
        $result = $file_full_base[2];

        return [
            'race_id' => $race_id,
            'position' => $position,
            'result' => $result
        ];
    }
}
