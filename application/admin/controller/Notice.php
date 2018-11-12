<?php
/**
 * Created by Joe.
 * Notice.php
 * 公告控制器
 * Create on: 2018/7/21 18:31
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Notice as NoticeModel;
use think\facade\Request;
use think\Db;

class Notice extends Base
{
    //访问添加公告界面
    public function AddNotice()
    {
        //用户是否登录
        $this->isLogin();
        $this->view->assign('title','发布公告');
        return $this->fetch();
    }
    //添加公告
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()){
            //获取公告信息
            $data = Request::post();
            $res = $this->validate($data,'app\common\Validate\Notice');
            if (true !== $res){
                echo '<script>alert("'.$res.'");window.history.go(-1);</script>';
            }else{
                if (NoticeModel::create($data)){
                    $this->success('公告添加成功','Notice/noticeList');
                }
                else{
                    $this->error('公告添加失败');
                }
            }
        }else{
            $this->error('请求类型错误');
        }
    }

    //获取公告通知列表
    public function noticeList()
    {
        //用户是否登录
        $this->isLogin();
        //实现搜索功能
        $keywords = Request::param('keywords');
        if (!empty($keywords)) {
            $map1 = ['title', 'like', '%' . $keywords . '%'];
            $map2 = ['content', 'like', '%' . $keywords . '%'];

            $noticeList = NoticeModel::whereOr([ $map1, $map2 ])
                ->paginate(7);

            //模板赋值
            $this->view->assign('title','公告管理');
            $this->view->assign('empty','<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('noticeList',$noticeList);
            //渲染出公告的列表模板
            return $this->view->fetch('noticeList');
        }
        $noticeList=NoticeModel::paginate(7);
        //模板赋值
        $this->view->assign('title','公告管理');
        $this->view->assign('empty','<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('noticeList',$noticeList);
        //渲染出公告的列表模板
        return $this->view->fetch('noticeList');
    }

    //编辑公告
    public function noticeEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的公告主键
        $noticeId = Request::param('nid');
        //根据主键进行查询
        $noticeInfo = NoticeModel::where('nid',$noticeId)->find();
        //设置编辑界面的模板变量
        $this->view->assign('title','编辑公告');
        $this->view->assign('noticeInfo',$noticeInfo);
        //渲染出编辑界面
        return $this->view->fetch('noticeedit');
    }
    //执行编辑公告的保存公告信息操作
    public function saveNotice()
    {
        //用户是否登录
        $this->isLogin();
        //获取公告提交的信息
        $data = Request::param();
        $res = $this->validate($data,'app\common\Validate\Notice');
        if (true !== $res){
            echo '<script>alert("'.$res.'");window.history.go(-1);</script>';
        }else{
            //获取主键
            $nid = $data['nid'];

            //删除主键
            unset($data['nid']);

            //执行更新操作
            if(Noticemodel::where('nid',$nid)->data($data)->Update()){
                return $this->success('更新成功','noticeList');
            }
            $this->error('没有更新或更新失败');
        }
    }
    //删除公告
    public function noticeDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $nid = Request::param('nid');

        //执行删除操作
        if(Noticemodel::where('nid',$nid)->delete()){
            return $this->success('删除成功','noticelist');
        }
        $this->error('删除失败');
    }
    //批量删除
    public function delall()
    {
        //用户是否登录
        $this->isLogin();
        $nid = $_POST['nid'];  //判断id是数组还是一个数值
        if (is_array($nid)) {
            $where = 'nid in(' . implode(',', $nid) . ')';
        } else {
            $where = 'nid=' . $nid;
        }  //dump($where);
        $list = NoticeModel::where($where)->delete();
        if ($list !== false) {
            $this->success("成功删除{$list}条！", "noticelist");
        } else {
            $this->error('删除失败！');
        }
    }
}