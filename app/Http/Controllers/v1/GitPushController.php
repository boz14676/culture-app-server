<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class GitPushController extends Controller
{
    /**
     * POST /git/push
     */
    public function push()
    {
        dd(123);
        $secret = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQClanCv4D4jjMcHkCIrCDjg35bV0TN4fxI6iUpF8VZF+3bR9muoOnjOV3YlvHd7+tPyfbodmZiVIwzGsVrGCt7m/0Xt71FoHYxpEamW7RTS9FK+R1qnRnbMI60HQ8bgrMjwbFzT12AKj8t6YuGyB1YgRYoShrOJmzbQpbzEW2/7vWmT2hx/IT27zJQumkv9xRCdTHU06ugVGkSjG67O0oURg9rtuny7YFTjLwLE/SV4BlinCa7Nm7Dj2EPDYyL3xiOlxy4QzJYZqzBFcohPKukPEFP5CRqZQfi/k13AfoOfGXFm0lLPWYt1hCnXHwJs4+WYNB6nNnRKryeG0jZz3Qnb zhangbo@zhangbodeMBP.lan';
        //获取http 头
        $headers = array();
        //Apache服务器才支持getallheaders函数
        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }
        //github发送过来的签名
        $hubSignature = $headers['X-Hub-Signature'];
        list($algo, $hash) = explode('=', $hubSignature, 2);

        // 获取body内容
        $payload = file_get_contents('php://input');

        // 计算签名
        $payloadHash = hash_hmac($algo, $payload, $secret);

        // 判断签名是否匹配
        if ($hash === $payloadHash) {
            //调用shell
            echo exec("/data/Git.sh");
        }
    }
}
