<?php
/**
 * Created by Joe.
 * Video.php
 * 视频表video模型
 * Create on: 2018/8/8 18:49
 */

namespace app\common\model;

use think\Model;
class Video extends Model
{
    protected $pk = 'id';     //默认主键
    protected $table = 'video'; //默认数据表

    protected $autoWriteTimestamp = true;  //自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y年m月d日';

    //开启自动设置
    protected $auto = [];     //无论是新增或者更新都要设置的字段
    //仅新增时设置
    protected $insert = ['create_time'];
    //仅更新时设置
    protected $update = ['update_time'];
}