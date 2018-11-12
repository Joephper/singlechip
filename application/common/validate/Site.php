<?php
/**
 * Created by Joe.
 * Site.php
 * 文件描述
 * Create on: 2018/8/22 9:56
 */

namespace app\common\validate;
use think\Validate;

class Site extends Validate
{
    protected $rule = [
        'name|网站名称' => 'require|length:2,20',
        'keywords|关键字' => 'require',
        'description|网站描述' => 'require',
    ];
}