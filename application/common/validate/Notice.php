<?php
/**
 * Created by Joe.
 * Notice.php
 * 公告表notice的验证器
 * Create on: 2018/7/21 17:10
 */

namespace app\common\validate;

use think\Validate;

class Notice extends Validate
{
    protected $rule = [
        'title|标题' => 'require|length:2,20',
        'content|内容' => 'require',
    ];
}