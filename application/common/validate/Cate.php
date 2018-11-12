<?php
/**
 * Created by Joe.
 * Cate.php
 * 文件描述
 * Create on: 2018/8/22 15:41
 */

namespace app\common\validate;
use think\Validate;

class Cate extends Validate
{
    protected $rule = [
        'name|章节名称' => 'require|length:2,20',
    ];
}