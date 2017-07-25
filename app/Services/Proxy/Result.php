<?php

/**
 *
 */

namespace App\Services\Proxy;

use Log;
use Cache;

use GuzzleHttp\Client;

class Result
{
    // 访问第三方接口获取数据
    public static function get($attributes)
    {
        extract($attributes);

        $client = new Client();
        $res = $client->request('POST', env('RESULT_HOST', ''), [
            'form_params' => [
                'race_id' => $race_id,
                'runner_no' => $runner_no
            ]
        ]);
        $content = $res->getBody()->getContents();
        if($content = \GuzzleHttp\json_decode($content)) {
            return $content;
        }

        return false;
    }
}
