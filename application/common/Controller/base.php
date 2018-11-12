<?php
/**
 * Created by Joe.
 * base.php
 * 公共控制器，公共方法的调用
 * Create on: 2018/7/21 16:45
 */

namespace app\common\Controller;

use think\Controller;
use think\facade\Session;
use app\common\model\Site;
use think\facade\Request;


class base extends Controller
{
    /* *
    *创建常量，公共方法
     * 在所有的方法之前被调用
    */
    protected function initialize()
    {
        //检测网站是否关闭
        $this->is_open();
    }

    //检查是否已登录：防止重复登录：放在登录验证方法调用
    public function Logined()
    {
        if (Session::has('user_id') && Session::get('active')==1) {
            $this->error('您好！您已经登录了', 'index/index');
        }
    }

    //检查是否未登录：放在需要登录操作的方法前面
    public function isLogin()
    {
        if (!Session::has('user_id')) {
            $this->error('您好！您还没有进行登录', 'user/login');
        }
    }

    //检测站点是否关闭
    public function is_open()
    {
        $isOpen = Site::where('status',1)->value('is_open');
        //站点关闭，关闭前台
        if ($isOpen == 0 && Request::module()=='index') {
            $info = <<< 'INFO'
<body><h1>站点维护中</h1></body>
INFO;
            exit($info);
        }
    }
    //检测注册是否关闭
    public function is_reg()
    {
        //当前注册状态
        $isReg=Site::where('status',1)->value('is_reg');
        if ($isReg==0){
            $this->error('系统已禁止注册用户','index/index');
        }
    }

}