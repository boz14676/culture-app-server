<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Validator;
use Log;
use App\Helper\Token;
use App\Helper\XXTEA;
use App\Models\BaseModel;
use Illuminate\Pagination\Paginator;

class Controller extends BaseController
{
    const SUCCESS         = 0;
    const UNKNOWN_ERROR   = 10000;
    const INVALID_SESSION = 10001;
    const EXPIRED_SESSION = 10002;

    const BAD_REQUEST     = 400;
    const UNAUTHORIZED    = 401;
    const NOT_FOUND       = 404;
    const IT_SERVER_ERROR = 500;
    public $validated;
    public $request;

    public function __construct() {
        $this->request = app('request');
    }

    /**
     * 验证输入信息
     * @param  array $rules
     * @return response
     */
    public function validateInput($rules)
    {
        $requests = $this->request->all();

        if (config('security.request_encrypt') && is_dev() == false) {
            if (in_array(strtolower($this->request->method()), ['put', 'post'])) {
                if (isset($requests['x'])) {
                    
                    if (!$x = XXTEA::decrypt(base64_decode($requests['x']))) {
                        return $this->error(self::BAD_REQUEST, trans('message.error.request_encrypt'));
                    }

                    parse_str($x, $requests);

                    if (is_array($requests)) {
                        foreach ($requests as $key => $value) {
                            $requests[$key] = urldecode($value);
                        }
                    }

                    if (isset($requests['page'])) {

                        $page = $requests['page'];
                        Paginator::currentPageResolver(function () use ($page) {
                            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                                return $page;
                            }

                            return 1;
                        });
                    }

                } elseif (!isset($requests['x']) && !empty($requests)) {
                    return $this->error(self::BAD_REQUEST, trans('message.error.request_encrypt'));
                }
            }
        }

        $validator = Validator::make($requests, $rules);
        if ($validator->fails()) {
            return $this->error(self::BAD_REQUEST, $validator->messages()->first());
        } else {
            $this->validated = array_intersect_key($requests, $rules);
            return false;
        }
    }

    /**
     * 返回Json数据
     * @param  array   $data
     * @param  array   $ext
     * @param  array   $paged
     * @return json
     */
    public function json($body = false)
    {
        //过滤null为空字符串(需协调客户端兼容)
        // if ($body) {
        //     $body = format_array($body);
        // }

        // 写入日志
        if (config('app.debug')) {

            $debug_id = uniqid();

            Log::debug($debug_id,[
                'LOG_ID'         => $debug_id,
                'IP_ADDRESS'     => $this->request->ip(),
                'REQUEST_URL'    => $this->request->fullUrl(),
                'AUTHORIZATION'  => $this->request->header('X-'.config('app.name').'-Authorization'),
                'REQUEST_METHOD' => $this->request->method(),
                'PARAMETERS'     => $this->validated,
                'RESPONSES'      => $body
            ]);

            $body['debug_id'] = $debug_id;
        }

        if (isset($body['error']) && $body['error']) {
            unset($body['error']);
            $response = response()->json($body);
            $response->header('X-'.config('app.name').'-ErrorCode', $body['error_code']);
            $response->header('X-'.config('app.name').'-ErrorDesc', urlencode($body['error_desc']));
        } else {
            $response = response()->json($body);
            $response->header('X-'.config('app.name').'-ErrorCode', 0);
        }

        if (config('token.refresh')) {
            if ($new_token = Token::refresh()) {
                // 生成新token
                $response->header('X-'.config('app.name').'-New-Authorization', $new_token);
            }
        }

        return $response;
    }
    
    public function formatPaged($page)
    {
        if ($page) {
            $data = $page->toArray()['data'];
            $paged = [
                'size' => $page->perPage(),
                'page' => $page->currentPage(),
                'more' => intval($page->hasMorePages())
            ];
        } else {
            $data = [];
            $paged = [
                'size' => 10,
                'page' => 1,
                'more' => 0
            ];
        }
        return $this->body([
            'data' => $data,
            'paged' => $paged
        ]);
    }

    /**
     * 格式化输出 Body
     * @param  array  $data 
     * @return json
     */
    public function body(array $data = [])
    {
        $data['error_code'] = 0;
        return $this->json($data);
    }

    /**
     * 格式化输出错误
     * @param  int $code
     * @param  string $message
     * @return json
     */
    public function error($code, $message = null)
    {
        switch ($code) {
            case self::UNKNOWN_ERROR:
                $message = trans('message.error.unknown');
                break;

            case self::NOT_FOUND:
                $message = trans('message.error.404');
                break;
        }

        $body['error'] = true;
        $body['error_code'] = $code;
        $body['error_desc'] = $message;
    
        return $this->json($body);
    }

}