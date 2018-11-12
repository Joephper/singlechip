<?php
/**
 * Created by Joe.
 * Index.php
 * 首页的控制器
 * Create on: 2018/7/21 16:45
 */
namespace app\index\controller;

use think\facade\Request;
use app\common\model\Notice;
use app\common\model\News;
use app\common\Controller\base;

class Index extends base
{
    //首页方法
    public function index()
    {
        //全局查询条件
        $map = [];//将所有查询条件封装到这个数组
        //条件1：
        $map[] = ['status','=',1];//这里等号不能省略

        //实现搜索功能
        $keywords = Request::param('keywords');
        if(!empty($keywords)){
            //条件2：
            $map[] = ['title','like','%'.$keywords.'%'];
        }

        //获取公告通知列表
        $NoticeList = Notice::all(function ($query) {
            $query->where('status', 1)->where('is_top',1)
                ->order('create_time', 'desc')
                ->limit(1);
        });
        if($NoticeList){
            $this->view->assign('NoticeList', $NoticeList);
        }else{
            $this->view->assign('empty','没有公告通知');
        }

        //获取新闻列表
        $newsList = News::all(function ($query) {
            $query->where('status', 1)
                ->order('create_time', 'desc')
                ->paginate(3);
        });
        if($newsList){
            $this->view->assign('newsList', $newsList);
        }else{
            $this->view->assign('empty','没有新闻');
        }


        $this->assign('title', '单片机教学网');
        return $this->fetch();
    }

    //公告详情页
    public function NoticeDetail()
    {
        $nid = Request::param('nid');
        $notice = Notice::get(function ($query) use ($nid){
           $query->where('nid','=',$nid)->setInc('pv');
        });
        if(!is_null($notice)){
            $this->view->assign('notice',$notice);
        }
        $this->view->assign('title','公告详情页');
        return $this->view->fetch('NoticeDetail');
    }

    //新闻详情页
    public function NewsDetail()
    {
        $id = Request::param('id');
        $news = News::get(function ($query) use ($id){
            $query->where('id','=',$id)->setInc('pv');
        });
        if(!is_null($news)){
            $this->view->assign('news',$news);
        }
        $this->view->assign('title','新闻详情');
        return $this->view->fetch('NewsDetail');
    }
}
