<?php
/**
 * Created by Joe.
 * News.php
 * 新闻数据模型
 * Create on: 2018/7/28 15:17
 */

namespace app\common\model;

use think\Model;

class News extends Model
{
    protected $pk = 'id';     //默认主键
    protected $table = 'news'; //默认数据表

    protected $autoWriteTimestamp = true;  //自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y.m.d';

    //开启自动设置
    protected $auto = [];     //无论是新增或者更新都要设置的字段
    //仅新增时设置
    protected $insert = ['create_time', 'is_top' => 0, 'status' => 1];
    //仅更新时设置
    protected $update = ['update_time'];
}