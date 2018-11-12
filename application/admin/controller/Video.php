<?php
/**
 * Created by Joe.
 * Video.php
 * 视频表video增删改查操作
 * Create on: 2018/8/9 14:08
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Video as VideoModel;
use app\common\model\Cate as CateModel;
use think\facade\Request;
use think\Db;
use think\facade\Session;

class Video extends Base
{
//访问添加视频界面
    public function AddVideo()
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
        $this->view->assign('title', '发布视频');
        return $this->fetch();
    }

    //添加视频
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()) {
            //获取视频信息
            $data = Request::post();
            $res = $this->validate($data, 'app\common\Validate\Video');
            if (true !== $res) {
                //验证失败
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                //验证成功
                //获取上传的视频信息
                $file = Request::file('path');
                $image = Request::file('image');
                //文件验证成功后上传到本地服务器指定目录，以public为起始目录
                if ($file == null || $image == null) {
                    echo '<script>window.history.go(-1);</script>';
                } else {
                    $info1 = $file->validate([
                        'size' => 1073741824,
                        'ext' => 'avi,wmv,mpeg,mp4,mov',
                    ])->move('videos/');
                    $info2 = $image->validate([
                        'size' => 10000000,
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
                    if (VideoModel::create($data)) {
                        $this->success('视频发布成功', 'video/videolist');
                    } else {
                        $this->error('视频发布失败', 'video/AddVideo');
                    }
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取视频列表
    public function videoList()
    {
        //用户是否登录
        $this->isLogin();
        if (Request::param('cid')) {
            $cid = Request::param('cid');
            Session::set('cid', $cid);


            $cate = new CateModel();

            $datas = $cate->cateTree();

            $this->view->assign('list',$datas);

            if (!empty($cid)) {
                $videoList = VideoModel::where('cid', $cid)->paginate(5);
                //模板赋值
                $this->view->assign('title', '学习教程管理');
                $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
                $this->view->assign('videoList', $videoList);
                //渲染出新闻的列表模板
                return $this->view->fetch('videoList');
            }
        }
        Session::delete('cid');

        $cate = new CateModel();
        $datas = $cate->cateTree();
        $this->view->assign('list',$datas);


        //实现搜索功能
        $keywords = Request::param('keywords');

        if (!empty($keywords)) {
            $map = ['title', 'like', '%' . $keywords . '%'];

            $videoList = VideoModel::whereOr([$map])
                ->paginate(5);

            //模板赋值
            $this->view->assign('title', '学习教程管理');
            $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('videoList', $videoList);
            //渲染出新闻的列表模板
            return $this->view->fetch('videoList');
        }



        $videoList = VideoModel::paginate(5);
        //模板赋值
        $this->view->assign('title', '学习教程管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('videoList', $videoList);
        //渲染出新闻的列表模板
        return $this->view->fetch('videoList');
    }

    //编辑视频
    public function videoEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的视频主键
        $videoId = Request::param('id');
        //根据主键进行查询
        $videoInfo = VideoModel::where('id', $videoId)->find();
        //获取章节外键


        $cate = new CateModel();
        $datas = $cate->cateTree(); //章节排序
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑视频');
        $this->view->assign('data', $datas);
        $this->view->assign('videoInfo', $videoInfo);
        //渲染出编辑界面
        return $this->view->fetch('videoedit');
    }

    //执行编辑视频的保存视频信息操作
    public function saveVideo()
    {
        //用户是否登录
        $this->isLogin();
        //获取视频提交的信息
        $data = Request::param();
        $path=Request::param('path');
        $image=Request::param('image');
        if ($path == null || $image==null)
        {
            echo '<script>window.history.go(-1);</script>';
            exit();
        }
        $res = $this->validate($data, 'app\common\Validate\Video');
        if (true !== $res) {
            //验证失败
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //删除原有的文件
            $id = Request::param('id');
            $data1 = VideoModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
            $path = 'videos/' . $data1;//定义文件存放的路径
            $data2 = VideoModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
            $image = 'images/' . $data2;//定义文件存放的路径
            if ($path) {
                unlink($path);
            }
            if ($image) {
                unlink($image);
            }

            //获取上传的视频信息
            $file = Request::file('path');
            $image = Request::file('image');
            //文件验证成功后上传到本地服务器指定目录，以public为起始目录
            if ($file == null || $image == null) {
                echo '<script>window.history.go(-1);</script>';
            } else {
                $info1 = $file->validate([
                    'size' => 1073741824,
                    'ext' => 'avi,wmv,mpeg,mp4,mov',
                ])->move('videos/');
                $info2 = $image->validate([
                    'size' => 10000000,
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
                if (VideoModel::update($data)) {
                    $this->success('视频更新成功', 'video/videolist');
                } else {
                    $this->error('视频更新失败');
                }
            }
        }
    }

    //删除视频
    public function videoDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');
        $data1 = VideoModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
        $path = 'videos/' . $data1;//定义文件存放的路径
        $data2 = VideoModel::where('id', $id)->value('image');//获取上传至本地服务器的文件路径
        $image = 'images/' . $data2;//定义文件存放的路径
        //执行删除操作
        if (Videomodel::where('id', $id)->delete()) {
            return unlink($path) && unlink($image) && $this->success('删除成功', 'videolist');
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
                $data = VideoModel::where('id', $int)->value('path');
                $path = 'videos/' . $data;//定义文件存放的路径

                $data1 = VideoModel::where('id', $int)->value('image');
                $image = 'images/' . $data1;//定义文件存放的路径

                if ($path) {
                    unlink($path);
                }
                if ($image) {
                    unlink($image);
                }
            }
        }
        $list = VideoModel::where($where)->delete();
        if ($list !== false) {

            return $this->success("成功删除{$list}条！", "videolist");
        } else {
            return $this->error('删除失败！');
        }
    }
}
