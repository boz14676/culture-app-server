<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class UserIdentification extends BaseModel
{
    protected $table = 'user_identifications';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = ['id'];

    protected $with = [];

    const STATUS_UNSUBMIT = 0;  // 未提交审核
    const STATUS_WAIT = 1;      // 等待管理员审核
    const STATUS_PASSED = 2;    // 审核通过
}
