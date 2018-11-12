<?php
/**
 * Created by Joe.
 * Code.php
 * 文件描述
 * Create on: 2018/8/31 16:09
 */

namespace app\common\validate;
use think\Validate;

class Code extends Validate
{
    protected $rule = [
        'code|校验码' => 'require',
    ];
}