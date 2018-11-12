<?php
/**
 * Created by Joe.
 * Password.php
 * 文件描述
 * Create on: 2018/8/29 14:15
 */

namespace app\common\validate;
use think\Validate;

class Password extends Validate
{
    protected $rule = [
        'oldpassword|旧密码' => 'require',
        'password|新密码' => 'require|length:6,20|alphaNum|confirm',
    ];
}