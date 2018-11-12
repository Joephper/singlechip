<?php
/**
 * Created by Joe.
 * Code.php
 * 文件描述
 * Create on: 2018/8/30 20:11
 */

namespace app\common\model;
use think\Model;

class Code extends Model
{
    protected $pk = 'id';     //默认主键
    protected $table = 'code'; //默认数据表
}