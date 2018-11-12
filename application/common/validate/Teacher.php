<?php
/**
 * Created by Joe.
 * Teacher.php
 * 文件描述
 * Create on: 2018/8/22 16:03
 */

namespace app\common\validate;
use think\Validate;

class Teacher extends Validate
{
    protected $rule = [
        'name|名字' => 'length:2,20|chsAlpha',
    ];
}