<?php
/**
 * Created by Joe.
 * Teacher.php
 * 文件描述
 * Create on: 2018/8/22 16:07
 */

namespace app\admin\controller;
use app\admin\common\Controller\Base;
use app\common\model\Teacher as TeacherModel;
use think\facade\Request;

class Teacher extends Base
{
//访问添加界面
    public function Add()
    {
        //用户是否登录
        $this->isLogin();
        $this->view->assign('title', '添加');
        return $this->fetch();
    }

    //添加资源
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()) {
            //获取资源信息
            $data = Request::post();
            $res = $this->validate($data, 'app\common\Validate\Teacher');
            if (true !== $res) {
                //验证失败
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                //验证成功
                //获取上传的文件信息
                $image = Request::file('image');
                //文件验证成功后上传到本地服务器指定目录，以public为起始目录
                if ($image == null) {
                    echo '<script>window.history.go(-1);</script>';
                } else {
                    $info = $image->validate([
                        'size' => 10000000,  //最大10M
                        'ext' => 'jpg,jpeg,png',
                    ])->move('images/');
                    if ($info) {
                        $data['image'] = $info->getSaveName();
                    } else {
                        $this->error($image->getError());
                    }
                    if (TeacherModel::create($data)) {
                        $this->success('添加成功', 'teacher/teacherlist');
                    } else {
                        $this->error('添加失败', 'teacher/Add');
                    }
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取列表
    public function TeacherList()
    {
        //用户是否登录
        $this->isLogin();
        $teacherList = TeacherModel::paginate(5);
        //模板赋值
        $this->view->assign('title', '教师简介管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('teacherList', $teacherList);
        //渲染出新闻的列表模板
        return $this->view->fetch('TeacherList');
    }

    //编辑
    public function Edit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的资源主键
        $Id = Request::param('id');
        //根据主键进行查询
        $teacherInfo = TeacherModel::where('id', $Id)->find();
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑');

        $this->view->assign('teacherInfo', $teacherInfo);
        //渲染出编辑界面
        return $this->view->fetch('edit');
    }

    //执行编辑资源的保存信息操作
    public function save()
    {
        //用户是否登录
        $this->isLogin();
        //获取提交的信息
        $data = Request::param();
        $res = $this->validate($data, 'app\common\Validate\Teacher');

        if (true !== $res) {
            //验证失败
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //删除原有的文件
            $id = Request::param('id');
            $data = TeacherModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
            $image = 'images/' . $data;//定义文件存放的路径

            if ($image) {
                unlink($image);
            }

            //获取上传的资源信息
            $image = Request::file('image');
            //文件验证成功后上传到本地服务器指定目录，以public为起始目录
            if ($image == null) {
                echo '<script>window.history.go(-1);</script>';
            } else {
                $info = $image->validate([
                    'size' => 10000000,  //最大10M
                    'ext' => 'jpg,jpeg,png',
                ])->move('images/');
                if ($info) {
                    $data['image'] = $info->getSaveName();
                } else {
                    $this->error($image->getError());
                }
                if (TeacherModel::update($data)) {
                    $this->success('更新成功', 'teacher/teacherlist');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    //删除
    public function Delete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');

        $data = TeacherModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
        $image = 'images/' . $data;//定义文件存放的路径
        //执行删除操作
        if (Teachermodel::where('id', $id)->delete()) {
            return unlink($image) && $this->success('删除成功', 'teacherlist');
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
        $arr = array($id);
        //遍历数组删除本地服务器上的文件
        foreach ($arr as $k => $v) {
            foreach ($v as $key => $val) {
                $arr1[] = $val;
                $int = (int)$val;  //强制类型转换

                $data = TeacherModel::where('id', $int)->value('image');
                $image = 'images/' . $data;//定义文件存放的路径

                if ($image) {
                    unlink($image);
                }
            }
        }
        $list = TeacherModel::where($where)->delete();
        if ($list !== false) {

            return $this->success("成功删除{$list}条！", "teacherlist");
        } else {
            return $this->error('删除失败！');
        }
    }
}