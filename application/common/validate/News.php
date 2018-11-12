<?php
/**
 * Created by Joe.
 * News.php
 * 新闻验证器
 * Create on: 2018/7/28 15:27
 */

namespace app\common\validate;
use think\Validate;

class News extends Validate
{
    protected $rule = [
        'title|标题' => 'require|length:2,20|chsAlphaNum',
        'content|内容' => 'require',
    ];
}