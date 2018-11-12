<?php
/**
 * Created by Joe.
 * Source.php
 * 文件描述
 * Create on: 2018/8/18 20:54
 */

namespace app\common\validate;
use think\Validate;

class Source extends Validate
{
    protected $rule = [
        'name|文件名' => 'length:2,20',
    ];
}