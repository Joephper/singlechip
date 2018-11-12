<?php
/**
 * Created by Joe.
 * Notice.php
 * 文件描述
 * Create on: 2018/7/21 16:45
 */

namespace app\common\model;

use think\Model;

class Notice extends Model
{
    protected $pk = 'nid';     //默认主键
    protected $table = 'notice'; //默认数据表

    protected $autoWriteTimestamp = true;  //自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y.m.d';

    //开启自动设置
    protected $auto = [];     //无论是新增或者更新都要设置的字段
    //仅新增时设置
    protected $insert = ['create_time', 'is_top' => 0, 'status' => 1];
    //仅更新时设置
    protected $update=['update_time'];
}