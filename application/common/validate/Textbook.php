<?php
/**
 * Created by Joe.
 * Textbook.php
 * 文件描述
 * Create on: 2018/8/10 20:13
 */

namespace app\common\validate;
use think\Validate;

class Textbook extends Validate
{
    protected $rule = [
        'title|教材标题' => 'length:2,20',
    ];
}