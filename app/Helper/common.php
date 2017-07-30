<?php

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}


if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}


if (! function_exists('bcrypt')) {
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    function bcrypt($value, $options = [])
    {
        return app('hash')->make($value, $options);
    }
}


if (! function_exists('end_with')) {
    /**
     * 第一个是原串,第二个是 部份串
     * @param  [type] $haystack [description]
     * @param  [type] $needle   [description]
     * @return [type]           [description]
     */
    function end_with($haystack, $needle)
    {
        $length = strlen($needle);
        if($length == 0)
        {
          return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

if (! function_exists('format_photo')) {
    /**
     * Format Photo
     *
     * @param  string $photo
     * @param  string $extra_path
     * @return array
     */
    function format_photo($img, $extra_path='')
    {
        if (!$img) {
            return ;
        }

        $cdn = config('app.cdn');

        if ((strpos($img, 'http://') === false)) {
            $img = $cdn . '/' . $extra_path . '/'. $img;
        }

        return $img;
    }
}

if (! function_exists('curl_request')) {
    /**
     * CURL Request
     */
    function curl_request($api, $method = 'GET', $params = array(), $headers = [])
    {
        $curl = curl_init();

        switch (strtoupper($method)) {
            case 'GET' :
                if (!empty($params)) {
                    $api .= (strpos($api, '?') ? '&' : '?') . http_build_query($params);
                }
                curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                break;
            case 'POST' :
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

                break;
            case 'PUT' :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case 'DELETE' :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if ($response === FALSE) {
            $error = curl_error($curl);
            curl_close($curl);
            return FALSE;
        }

        curl_close($curl);

        return $response;
    }
}

if (! function_exists('show_error')) {
    /**
     * Show Error
     */
    function show_error($code, $message)
    {
        $response = response()->json([
            'error_code' => $code,
            'error_desc' => $message
        ]);
        $response->header('X-'.config('app.name').'-ErrorCode', $code);
        $response->header('X-'.config('app.name').'-ErrorDesc', urlencode($message));
        return $response;
    }
}

if (! function_exists('keyToPem')) {
    /**
     * key To Pem
     */
    function keyToPem($key, $private=false)
    {
        //Split lines:
        $lines = str_split($key, 65);
        $body = implode("\n", $lines);
        //Get title:
        $title = $private? 'RSA PRIVATE KEY' : 'PUBLIC KEY';
        //Add wrapping:
        $result = "-----BEGIN {$title}-----\n";
        $result .= $body . "\n";
        $result .= "-----END {$title}-----\n";

        return $result;
    }
}

if (! function_exists('unserialize_config')) {
    /**
     * 处理序列化的支付、配送的配置参数
     * 返回一个以name为索引的数组
     *
     * @access  public
     * @param   string       $cfg
     * @return  void
     */
    function unserialize_config($cfg)
    {
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
        {
            $config = array();

            foreach ($arr AS $key => $val)
            {
                $config[$val['name']] = $val['value'];
            }

            return $config;
        }
        else
        {
            return false;
        }
    }
}

if (! function_exists('is_dev')) {
    function is_dev()
    {
        if (app('request')->cookie('debug_key') == config('security.debug_key')) {
            return true;
        }

        return false;
    }
}

if (! function_exists('ticksToTimeStr')) {
    function ticksToTimeStr($ticks,$accuracy=0)
    {
        if($ticks==null)
            return "";
        $tt=$ticks;
        $second=$tt%60;
        $tt=floor($tt/60);
        $min=$tt%60;
        $hour=floor($tt/60);
        $second=str_pad($second,2,'0',STR_PAD_LEFT);
        $min=str_pad($min,2,'0',STR_PAD_LEFT);
        $hour=str_pad($hour,2,'0',STR_PAD_LEFT);

        return $hour.":".$min.":".$second;
    }

}

// 下划线转驼峰
if (! function_exists('camelize')) {
    function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }
}

// 驼峰命名转下划线
if (! function_exists('uncamelize')) {
    function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}