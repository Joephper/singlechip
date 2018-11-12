<?php

/**
 * Created by Joe.
 * Base.php
 * 后台公共控制器
 * Create on: 2018/7/23 10:56
 */
namespace app\admin\common\controller;

use think\Controller;
use think\facade\Session;
use app\common\model\Code as CodeModel;
use app\common\model\User as UserModel;


class Base extends Controller
{
    protected function initialize()
    {

    }

    /**
     * 检查用户是否登录
     *1.调用位置：后台入口：admin.php/index/index()
     */
    public function isLogin()
    {
        if (!Session::has('user_id')) {
            $this->error('请先登录', 'admin/user/login');
        }
    }



}