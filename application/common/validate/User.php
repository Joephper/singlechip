<?php
/**
 * Created by Joe.
 * User.php
 * 用户表user的验证器
 * Create on: 2018/7/21 16:45
 */

namespace app\common\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'name|昵称' => 'require|length:2,20|chsDash|unique:user',
        'password|密码' => 'require|length:6,20|alphaNum|confirm',
        'email|邮箱' => 'require|email|unique:user',
        'mobile|手机号' => 'mobile',
    ];
}