<?php
/**
 * Created by Joe.
 * Classes.php
 * 文件描述
 * Create on: 2018/8/16 19:54
 */

namespace app\admin\controller;
use app\admin\common\Controller\Base;
use app\common\model\Classes as ClassModel;
use think\facade\Request;

class Classes extends Base
{
    //访问添加类别界面
    public function AddClass()
    {
        //用户是否登录
        $this->isLogin();
        $this->view->assign('title', '添加类别');
        return $this->fetch();
    }

    //添加类别
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()) {
            //获取类别信息
            $data = Request::post();
            $res = $this->validate($data, 'app\common\Validate\Classes');
            if (true !== $res) {
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                if (ClassModel::create($data)) {
                    $this->success('类别添加成功', 'Classes/classList');
                } else {
                    $this->error('类别添加失败');
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取列表
    public function classList()
    {
        //用户是否登录
        $this->isLogin();
        $classList = ClassModel::paginate(7);
        //模板赋值
        $this->view->assign('title', '类别管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('classList', $classList);
        //渲染出列表模板
        return $this->view->fetch('classList');
    }

    //编辑类别
    public function classEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的类别主键
        $classId = Request::param('id');
        //根据主键进行查询
        $classInfo = ClassModel::where('id', $classId)->find();
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑类别');
        $this->view->assign('classInfo', $classInfo);
        //渲染出编辑界面
        return $this->view->fetch('classedit');
    }

    //执行编辑类别的保存类别信息操作
    public function saveClass()
    {
        //用户是否登录
        $this->isLogin();
        //获取类别提交的信息
        $data = Request::post();
        $res = $this->validate($data, 'app\common\Validate\Classes');
        if (true !== $res) {
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        }else{
            //获取主键
            $id = $data['id'];

            //删除主键
            unset($data['id']);

            //执行更新操作
            if (Classmodel::where('id', $id)->data($data)->Update()) {
                return $this->success('更新成功', 'classList');
            }
            $this->error('没有更新或更新失败');
        }
    }

    //删除类别
    public function classDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');


        //执行删除操作
        if (Classmodel::where('id', $id)->delete()) {
            return $this->success('删除成功', 'classlist');
        }
        $this->error('删除失败');
    }

    //批量删除
    public function delall()
    {
        //用户是否登录
        $this->isLogin();
        $id = $_POST['id'];  //判断id是数组还是一个数值
        if (is_array($id)) {
            $where = 'id in(' . implode(',', $id) . ')';
        } else {
            $where = 'id=' . $id;
        }  //dump($where);
        $list = ClassModel::where($where)->delete();
        if ($list !== false) {
            $this->success("成功删除{$list}条！", "classlist");
        } else {
            $this->error('删除失败！');
        }
    }
}