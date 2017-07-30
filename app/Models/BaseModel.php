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
        DB::enableQueryLog();
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
     * repositories
     * @param int $per_page             # 每页显示记录数
     * @param array $q                  # 筛选
     * @param array $s                  # 排序
     * @return mixed                    # 实体对象或null
     */
    public static function repositories($per_page=10, $q=[], $s=[])
    {
        return self
            // 排序
            ::when($s, function ($query) use ($s) {
                return self::sorting($query, $s);
            })
            // 筛选
            ->when($q, function ($query) use ($q) {
                return self::filtering($query, $q);
            })

            ->simplePaginate($per_page);
    }

    /**
     * 过滤
     * @param object $query
     * @param array $q
     * @return mixed
     */
    public static function filtering($query, array $q=[])
    {
        collect($q)->map(function ($item, $key) use (&$query) {
            // 关键字筛选
            if ($key === 'keywords') {
                $query->where('name', 'like', '%' . $item . '%');
            } else {
                if (is_array($item) || is_object($item))
                    $query->whereIn($key, $item);
                else
                    $query->where($key, $item);
            }
        });

        return $query;
    }

    /**
     * 排序
     * @param object $query
     * @param array $q
     * @return mixed
     */
    public static function sorting($query, array $s=[])
    {
        collect($s)->map(function ($item, $key) use (&$query) {
            $query->orderBy($key, $item);
        });

        return $query;
    }

    public static function formatBody(array $data = [])
    {
        $data['error_code'] = 0;
        return $data;
    }
}