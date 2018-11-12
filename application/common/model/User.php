<?php
/**
 * Created by Joe.
 * User.php
 * 用户模型
 * Create on: 2018/7/21 16:45
 */

namespace app\common\model;

use think\Model;

class User extends Model
{
    protected $pk = 'id';     //默认主键
    protected $table = 'user'; //默认数据表

    protected $autoWriteTimestamp = true;  //自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y年m月d日H时m分s秒';

    //开启自动设置
    protected $auto = [];     //无论是新增或者更新都要设置的字段
    //仅更新时设置
    protected $update=['update_time'];

    //获取器
    public function getStatusAttr($value)
    {
        $status = ['1' => '启用', '0' => '禁用'];
        return $status[$value];
    }

    public function getRoleAttr($value)
    {
        $status = ['2' => '超级管理员', '1' => '超级会员', '0' => '普通会员'];
        return $status[$value];
    }

    //修改器
    public function setPasswordAttr($value)
    {
        return sha1($value);
    }
}