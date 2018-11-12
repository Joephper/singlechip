<?php
/**
 * Created by Joe.
 * Email.php
 * 文件描述
 * Create on: 2018/8/30 18:07
 */

namespace app\common\validate;
use think\Validate;

class Email extends Validate
{
    protected $rule = [
        'email|邮箱' => 'require|email',
        'verify|验证码' => 'require',
    ];
}