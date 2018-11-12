<?php
/**
 * Created by Joe.
 * Video.php
 * 文件描述
 * Create on: 2018/8/9 16:50
 */

namespace app\common\validate;
use think\Validate;

class Video extends Validate
{
    protected $rule = [
        'title|视频标题' => 'length:2,20',
    ];
}