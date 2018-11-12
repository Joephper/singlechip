<?php
/**
 * Created by Joe.
 * Site.php
 * 文件描述
 * Create on: 2018/8/19 22:16
 */

namespace app\admin\controller;

use app\admin\common\controller\Base;
use app\common\model\Site as Sitemodel;
use think\facade\Request;

class Site extends Base
{
    public function Index()
    {
        //用户是否登录
        $this->isLogin();
        $siteInfo = Sitemodel::get(['status' => 1]);
        $this->view->assign('siteInfo', $siteInfo);
        $this->view->assign('title', '系统');
        return $this->view->fetch('index');
    }

    public function Save()
    {
        //用户是否登录
        $this->isLogin();
        //获取类别提交的信息
        $data = Request::param();
        $res = $this->validate($data, 'app\common\Validate\Site');
        if (true !== $res) {
            //验证失败
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //获取主键
            $id = $data['id'];

            //删除主键
            unset($data['id']);

            //执行更新操作
            if (Sitemodel::where('id', $id)->data($data)->Update()) {
                return $this->success('更新成功', 'index');
            }
            $this->error('没有更新或更新失败');
        }


    }
}