<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\User;

class UserController extends Controller
{
    /**
     * POST /urun.get
     */
    public function get()
    {
        $user = User::get();
        return $this->body(['user' => $user]);
    }

    /**
     * POST /urun.auth.social
     */
    public function weappAuth()
    {
        $rules = [
            'code' => 'required|string',
            'rawData'  => 'required|string',
            'signature' => 'required|string',
            'encryptedData' => 'required|string',
            'iv'  => 'required|string',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $auth_attributes = User::weappAuth($this->validated);
        
        return $this->body($auth_attributes);
    }
    
    /**
     * POST /urun.user.mobile.send
     */
    public function sendVerifyCode(Request $request)
    {
        $rules = [
            'mobile'  => 'required|integer|unique:users,mobile',
        ];
    
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        $res = User::sendVerifyCode($request->input('mobile'));
        if ($res) {
            return $this->body();
        } else {
            return $this->error(self::BAD_REQUEST, trans('message.user.send_code_error'));
        }
        
    }
    
    /**
     * POST /urun.user.mobile.bind
     */
    public function bind_mobile(Request $request)
    {
        $rules = [
            'mobile'  => 'required|integer',
            'verify_code'  => 'required|integer'
        ];
    
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        $res = User::bind_mobile($request->input('mobile'), $request->input('verify_code'));
        switch ($res) {
            case 101:
                return $this->error(self::UNAUTHORIZED, trans('message.user.verify_code_error'));
                break;
            default:
                return $this->body();
        }
    }
    
    /**
     * GET /urun.user.race.lists
     */
    public function raceLists(Request $request)
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        $user_races = User::raceLists($request->input('per_page'));
        return $this->formatPaged($user_races);
    }
}
