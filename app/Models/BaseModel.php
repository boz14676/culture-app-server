<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class BaseModel extends Model
{
    const SUCCESS         = 0;
    const UNKNOWN_ERROR   = 10000;
    const INVALID_SESSION = 10001;
    const EXPIRED_SESSION = 10002;

    const BAD_REQUEST     = 400;
    const UNAUTHORIZED    = 401;
    const NOT_FOUND       = 404;
    const MOBILE_USE      = 405;
    const INTERNAL_SERVER_ERROR = 500;

    protected $casts = [
        'id' => 'string',
    ];
    
    protected static $error_msg = ''; // 错误信息
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // DB::enableQueryLog();
    }
    
    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    public $timestamps = true;
    
    /**
     * 查找某场赛事的信息
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBelongsRace($query, $race_id)
    {
        return $query->where('race_id', $race_id);
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
    
    /**
     * 获取当前时间
     *
     * @return int
     */
    /*public function freshTimestamp() {
        return time();
    }*/

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */
    public function fromDateTime($value) {
        return $value;
    }

    public static function formatPaged($page)
    {
        return [
            'total' => $page->total(),
            'page' => $page->currentPage(),
            'size' => $page->perPage(),
            'more' => intval($page->hasMorePages())
        ];
    }

    public static function formatBody(array $data = [])
    {
        $data['error_code'] = 0;
        return $data;
    }

    public static function formatError($code, $message = null)
    {
        switch ($code) {
            case self::UNKNOWN_ERROR:
                $message = trans('message.error.unknown');
                break;

            case self::NOT_FOUND:
                $message = $message ? : trans('message.error.404');
                break;
        }

        $body['error'] = true;
        $body['error_code'] = $code;
        $body['error_desc'] = $message;

        return $body;
    }
}