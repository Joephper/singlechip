<?php
/**
 * Created by Joe.
 * NewPassword.php
 * 文件描述
 * Create on: 2018/8/31 18:09
 */

namespace app\common\validate;
use think\Validate;

class NewPassword extends Validate
{
    protected $rule = [
        'password|密码' => 'require|length:6,20|alphaNum|confirm',
    ];
}