<?php
/**
 * Created by Joe.
 * Textbook.php
 * 电子教材控制器
 * Create on: 2018/8/10 20:09
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Textbook as TextModel;
use app\common\model\Cate as CateModel;
use think\facade\Request;
use think\Db;
use think\facade\Session;


class Textbook extends Base
{
//访问添加电子教材界面
    public function AddText()
    {
        //用户是否登录
        $this->isLogin();
        //获取章节的信息
        $cate = new CateModel();
        $datas = $cate->cateTree(); //章节排序
        if (count($datas) > 0) {
            $this->assign('CateList', $datas);
        } else {
            $this->error('请先添加章节', 'Cate/add');
        }
        $this->view->assign('title', '发布电子教材');
        return $this->fetch();
    }

    //添加电子教材
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()) {
            //获取视频信息
            $data = Request::post();
            $res = $this->validate($data, 'app\common\Validate\Textbook');
            if (true !== $res) {
                //验证失败
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                //验证成功
                //获取上传的视频信息
                $file = Request::file('path');
                //文件验证成功后上传到本地服务器指定目录，以public为起始目录
                if ($file == null) {
                    echo '<script>window.history.go(-1);</script>';
                } else {
                    $info = $file->validate([
                        'size' => 1073741824,
                        'ext' => 'pdf',
                    ])->move('pdf/');
                    if ($info) {
                        $data['path'] = $info->getSaveName();
                    } else {
                        $this->error($file->getError());
                    }
                    if (TextModel::create($data)) {
                        $this->success('电子教材发布成功', 'textbook/textlist');
                    } else {
                        $this->error('电子教材发布失败', 'textbook/AddText');
                    }
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取电子教材列表
    public function textList()
    {
        //用户是否登录
        $this->isLogin();
        if (Request::param('cid')) {
            $cid = Request::param('cid');
            Session::set('cid', $cid);


            $list = CateModel::all();

            $cate = new CateModel();

            $datas = $cate->cateTree();
            $this->view->assign('data',$list);
            $this->view->assign('list',$datas);

            if (!empty($cid)) {
                $TextList = TextModel::where('cid', $cid)->paginate(5);
                //模板赋值
                $this->view->assign('title', '学习教程管理');
                $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
                $this->view->assign('textList', $TextList);
                //渲染出新闻的列表模板
                return $this->view->fetch('textList');
            }
        }
        Session::delete('cid');
        $list = CateModel::all();
        $cate = new CateModel();

        $datas = $cate->cateTree();
        $this->view->assign('data',$list);
        $this->view->assign('list',$datas);

        //实现搜索功能
        $keywords = Request::param('keywords');

        if (!empty($keywords)) {
            $map = ['title', 'like', '%' . $keywords . '%'];

            $textList = TextModel::whereOr([$map])
                ->paginate(7);

            //模板赋值
            $this->view->assign('title', '学习教程');
            $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('textList', $textList);
            //渲染出电子教材的列表模板
            return $this->view->fetch('textList');
        }

        $textList = TextModel::paginate(7);
        //模板赋值
        $this->view->assign('title', '学习教程');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('textList', $textList);
        //渲染出电子教材的列表模板
        return $this->view->fetch('textList');
    }

    //编辑电子教材
    public function textEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的电子教材主键
        $textId = Request::param('id');
        //根据主键进行查询
        $textInfo = TextModel::where('id', $textId)->find();
        //获取章节外键

        $cate = new CateModel();
        $datas = $cate->cateTree(); //章节排序
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑电子教材');
        $this->view->assign('data', $datas);
        $this->view->assign('textInfo', $textInfo);
        //渲染出编辑界面
        return $this->view->fetch('textedit');
    }

    //执行编辑电子教材的保存视频信息操作
    public function saveText()
    {
        //用户是否登录
        $this->isLogin();
        //获取电子教材提交的信息
        $data = Request::param();
        $path=Request::param('path');
        if ($path == null)
        {
            echo '<script>window.history.go(-1);</script>';
            exit();
        }
        $res = $this->validate($data, 'app\common\Validate\Textbook');
        if (true !== $res) {
            //验证失败
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //删除原有的文件
            $id = Request::param('id');
            $data = TextModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
            $path = 'pdf/' . $data;//定义文件存放的路径
            if ($path) {
                unlink($path);
            }

            //获取上传的pdf信息
            $file = Request::file('path');
            if ($file == null) {
                echo '<script>window.history.go(-1);</script>';
            } else {
                //文件验证成功后上传到本地服务器指定目录，以public为起始目录
                $info = $file->validate([
                    'size' => 1073741824, //文件大小不超过1G
                    'ext' => 'pdf',
                ])->move('pdf/');
                if ($info) {
                    $data['path'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
                if (TextModel::update($data)) {
                    $this->success('pdf更新成功', 'textbook/textlist');
                } else {
                    $this->error('pdf更新失败');
                }
            }
        }
    }

    //删除电子教材
    public function textDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');
        $data = TextModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
        $path = 'pdf/' . $data;//定义文件存放的路径

        //执行删除操作
        if (Textmodel::where('id', $id)->delete()) {
            return unlink($path) && $this->success('删除成功', 'textlist');
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
                $data = TextModel::where('id', $int)->value('path');
                $path = 'pdf/' . $data;//定义文件存放的路径

                if ($path) {
                    unlink($path);
                }
            }
        }
        $list = TextModel::where($where)->delete();
        if ($list !== false) {

            return $this->success("成功删除{$list}条！", "textlist");
        } else {
            return $this->error('删除失败！');
        }
    }

}

