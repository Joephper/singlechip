<?php
/**
 * Created by Joe.
 * Classes.php
 * 文件描述
 * Create on: 2018/8/16 19:59
 */

namespace app\common\validate;
use think\Validate;

class Classes extends Validate
{
    protected $rule = [
        'name|类别名' => 'require|length:2,20|chsAlpha',
    ];
}