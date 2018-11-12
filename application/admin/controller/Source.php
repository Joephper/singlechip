<?php
/**
 * Created by Joe.
 * Source.php
 * 文件描述
 * Create on: 2018/8/18 20:42
 */

namespace app\admin\controller;
use app\admin\common\Controller\Base;
use app\common\model\Classes as ClassModel;
use app\common\model\Source as SourceModel;
use think\facade\Request;
use think\facade\Session;

class Source extends Base
{
//访问添加资源界面
    public function AddSource()
    {
        //用户是否登录
        $this->isLogin();
        //获取类别的信息
        $ClassList = ClassModel::all();
        if (count($ClassList) > 0) {
            $this->assign('ClassList', $ClassList);
        } else {
            $this->error('请先添加类别', 'Classes/AddClass');
        }
        $this->view->assign('title', '添加资源');
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
            $res = $this->validate($data, 'app\common\Validate\Source');
            if (true !== $res) {
                //验证失败
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                //验证成功
                //获取上传的文件信息
                $file = Request::file('path');
                $image = Request::file('image');
                //文件验证成功后上传到本地服务器指定目录，以public为起始目录
                if ($file == null || $image == null) {
                    echo '<script>window.history.go(-1);</script>';
                } else {
                    $info1 = $file->validate([
                        'size' => 2000000000, //最大2G
                        'ext' => 'rar,zip,pdf,doc,docx,ppt,pptx',
                    ])->move('uploads/');
                    $info2 = $image->validate([
                        'size' => 10000000,  //最大10M
                        'ext' => 'jpg,jpeg,png',
                    ])->move('images/');
                    if ($info1) {
                        $data['path'] = $info1->getSaveName();
                    } else {
                        $this->error($file->getError());
                    }
                    if ($info2) {
                        $data['image'] = $info2->getSaveName();
                    } else {
                        $this->error($image->getError());
                    }
                    if (SourceModel::create($data)) {
                        $this->success('资源添加成功', 'source/sourcelist');
                    } else {
                        $this->error('资源添加失败', 'source/AddSource');
                    }
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取资源列表
    public function SourceList()
    {
        //用户是否登录
        $this->isLogin();
        if (Request::param('cid')) {
            $cid = Request::param('cid');
            Session::set('cid', $cid);


            $classlist = ClassModel::all();
            $this->view->assign('classlist',$classlist);

            if (!empty($cid)) {
                $sourceList = SourceModel::where('cid', $cid)->paginate(5);
                //模板赋值
                $this->view->assign('title', '资源管理');
                $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
                $this->view->assign('sourceList', $sourceList);
                //渲染出新闻的列表模板
                return $this->view->fetch('SourceList');
            }
        }
        Session::delete('cid');
        $classlist = ClassModel::all();
        $this->view->assign('classlist',$classlist);


        //实现搜索功能
        $keywords = Request::param('keywords');

        if (!empty($keywords)) {
            $map1 = ['name', 'like', '%' . $keywords . '%'];
            $map2 = ['description', 'like', '%' . $keywords . '%'];

            $sourceList = SourceModel::whereOr([$map1,$map2])
                ->paginate(5);

            //模板赋值
            $this->view->assign('title', '资源管理');
            $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('sourceList', $sourceList);
            //渲染出新闻的列表模板
            return $this->view->fetch('SourceList');
        }



        $sourceList = SourceModel::paginate(5);
        //模板赋值
        $this->view->assign('title', '学习教程管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('sourceList', $sourceList);
        //渲染出新闻的列表模板
        return $this->view->fetch('SourceList');
    }

    //编辑资源
    public function sourceEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的资源主键
        $sourceId = Request::param('id');
        //根据主键进行查询
        $sourceInfo = SourceModel::where('id', $sourceId)->find();
        //获取类别外键
        //获取类别信息

        $ClassList=ClassModel::all();

      
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑资源');
        $this->view->assign('ClassList', $ClassList);

        $this->view->assign('sourceInfo', $sourceInfo);
        //渲染出编辑界面
        return $this->view->fetch('sourceedit');
    }

    //执行编辑资源的保存资源信息操作
    public function saveSource()
    {
        //用户是否登录
        $this->isLogin();
        //获取资源提交的信息
        $data = Request::param();
        $path=Request::param('path');
        $image=Request::param('image');
        if ($path == null || $image==null)
        {
            echo '<script>window.history.go(-1);</script>';
            exit();
        }
        $res = $this->validate($data, 'app\common\Validate\Source');
        if (true !== $res) {
            //验证失败
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //删除原有的文件
            $id = Request::param('id');
            $data1 = SourceModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
            $path = 'uploads/' . $data1;//定义文件存放的路径
            $data2 = SourceModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
            $image = 'images/' . $data2;//定义文件存放的路径
            if ($path) {
                unlink($path);
            }
            if ($image) {
                unlink($image);
            }

            //获取上传的资源信息
            $file = Request::file('path');
            $image = Request::file('image');
            //文件验证成功后上传到本地服务器指定目录，以public为起始目录
            if ($file == null || $image == null) {
                echo '<script>window.history.go(-1);</script>';
            } else {
                $info1 = $file->validate([
                    'size' => 2000000000, //最大2G
                    'ext' => 'rar,zip,pdf,doc,docx,ppt,pptx',
                ])->move('uploads/');
                $info2 = $image->validate([
                    'size' => 10000000,  //最大10M
                    'ext' => 'jpg,jpeg,png',
                ])->move('images/');
                if ($info1) {
                    $data['path'] = $info1->getSaveName();
                } else {
                    $this->error($file->getError());
                }
                if ($info2) {
                    $data['image'] = $info2->getSaveName();
                } else {
                    $this->error($image->getError());
                }
                if (SourceModel::update($data)) {
                    $this->success('资源更新成功', 'source/sourcelist');
                } else {
                    $this->error('资源更新失败');
                }
            }
        }
    }

    //删除资源
    public function sourceDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');
        $data1 = SourceModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
        $path = 'uploads/' . $data1;//定义文件存放的路径
        $data2 = SourceModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
        $image = 'images/' . $data2;//定义文件存放的路径
        //执行删除操作
        if (Sourcemodel::where('id', $id)->delete()) {
            return unlink($path) && unlink($image) && $this->success('删除成功', 'sourcelist');
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
                $data = SourceModel::where('id', $int)->value('path');
                $path = 'uploads/' . $data;//定义文件存放的路径

                $data1 = SourceModel::where('id', $int)->value('image');
                $image = 'images/' . $data1;//定义文件存放的路径

                if ($path) {
                    unlink($path);
                }
                if ($image) {
                    unlink($image);
                }
            }
        }
        $list = SourceModel::where($where)->delete();
        if ($list !== false) {

            return $this->success("成功删除{$list}条！", "sourcelist");
        } else {
            return $this->error('删除失败！');
        }
    }
}