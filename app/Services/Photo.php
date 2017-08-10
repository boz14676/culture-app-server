<?php

namespace App\Services;

use Image;

class Photo
{
    protected static $error_msg = ''; // 错误信息

    /**
     * 上传图片
     */
    public static function uploads($photo) {
        if ($photo->isValid()) {
            $filename = 'avatar-' . rand(111111,999999) . '.' . $photo->getClientOriginalExtension();

            $img = Image::make($photo);
            $img_ratio = round($img->width() / $img->height(), 2);
            $expect_width = 500;
            $img->resize($expect_width, $expect_width / $img_ratio);
            $img->save(app()->basePath().'/public/file/photos/user/'.$filename);
        } else {
            self::errorMsg(trans('message.error.upload_failed'));

            return false;
        }

        return $filename;
    }

    // errorMsg [set&get]
    public static function errorMsg($error_msg = '')
    {
        // set
        if ($error_msg) {
            self::$error_msg = $error_msg;
            return true;
        }
        // get
        else
        {
            if (self::$error_msg) {
                return self::$error_msg;
            }

            return '';
        }
    }
}
