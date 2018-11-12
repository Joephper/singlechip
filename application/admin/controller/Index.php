<?php
namespace app\admin\controller;
use app\admin\common\Controller\Base;

/**
 * Created by Joe.
 * Index.php
 * 后台首页
 * Create on: 2018/7/23 10:24
 */
class Index extends Base
{
    public function index()
    {
        //用户是否登录
        $this->isLogin();
        return $this->redirect('site/index'); //直接跳转至用户列表
    }
    public function lst()
    {
        return $this->fetch();
    }
}
