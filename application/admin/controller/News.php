<?php
/**
 * Created by Joe.
 * News.php
 * 后台管理新闻表news控制器
 * Create on: 2018/7/28 15:30
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\News as NewsModel;
use think\facade\Request;
use think\Db;

class News extends Base
{
    //访问添加新闻界面
    public function AddNews()
    {
        //用户是否登录
        $this->isLogin();
        $this->view->assign('title', '发布新闻');
        return $this->fetch();
    }

    //添加新闻
    public function insert()
    {
        //用户是否登录
        $this->isLogin();
        //判断提交类型
        if (Request::isPost()) {
            //获取新闻信息
            $data = Request::post();
            $res = $this->validate($data, 'app\common\Validate\News');
            if (true !== $res) {
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            } else {
                //获取上传的文件信息
                $image = Request::file('title_img');
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
                    if (NewsModel::create($data)) {
                        $this->success('新闻添加成功', 'News/newsList');
                    } else {
                        $this->error('新闻添加失败');
                    }
                }
            }
        } else {
            $this->error('请求类型错误');
        }
    }

    //获取新闻通知列表
    public function newsList()
    {
        //用户是否登录
        $this->isLogin();
        //实现搜索功能
        $keywords = Request::param('keywords');
        if (!empty($keywords)) {
            $map1 = ['title', 'like', '%' . $keywords . '%'];
            $map2 = ['content', 'like', '%' . $keywords . '%'];

            $newsList = NewsModel::whereOr([$map1, $map2])
                ->paginate(7);

            //模板赋值
            $this->view->assign('title', '新闻管理');
            $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('newsList', $newsList);
            //渲染出新闻的列表模板
            return $this->view->fetch('newsList');
        }

        $newsList = NewsModel::paginate(7);
        //模板赋值
        $this->view->assign('title', '新闻管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('newsList', $newsList);
        //渲染出新闻的列表模板
        return $this->view->fetch('newsList');
    }

    //编辑公告
    public function newsEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的新闻主键
        $newsId = Request::param('id');
        //根据主键进行查询
        $newsInfo = NewsModel::where('id', $newsId)->find();
        //设置编辑界面的模板变量
        $this->view->assign('title', '编辑新闻');
        $this->view->assign('newsInfo', $newsInfo);
        //渲染出编辑界面
        return $this->view->fetch('newsedit');
    }

    //执行编辑新闻的保存新闻信息操作
    public function saveNews()
    {
        //用户是否登录
        $this->isLogin();
        //获取新闻提交的信息
        $data = Request::param();
        $res = $this->validate($data, 'app\common\Validate\News');
        if (true !== $res) {
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            //获取主键
            $id = $data['id'];

            //删除主键
            unset($data['id']);

            //执行更新操作
            if (Newsmodel::where('id', $id)->data($data)->Update()) {
                return $this->success('更新成功', 'newsList');
            }
            $this->error('没有更新或更新失败');
        }
    }

    //删除新闻
    public function newsDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');


        //执行删除操作
        if (Newsmodel::where('id', $id)->delete()) {
            return $this->success('删除成功', 'newslist');
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
        $list = NewsModel::where($where)->delete();
        if ($list !== false) {
            $this->success("成功删除{$list}条！", "newslist");
        } else {
            $this->error('删除失败！');
        }
    }
}