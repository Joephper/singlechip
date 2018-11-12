<?php
/**
 * Created by Joe.
 * Learn.php
 * 文件描述
 * Create on: 2018/8/9 14:48
 */

namespace app\index\controller;
use app\common\model\Cate as cateModel;
use app\common\model\Video as videoModel;
use app\common\model\Textbook as textModel;
use app\common\Controller\base;
use think\facade\Request;

class Learn extends base
{
    //教学视频
    public function video()
    {
        $cate = new cateModel();
        $data = $cate->cateTree();
        if($data){
            $this->view->assign('CateList', $data);
        }

        $id = Request::param('id');
        $video = videoModel::all(function ($query) use ($id){
            $query->where('cid','=',$id);
        });
        if(!is_null($video)){
            $this->view->assign('video',$video);
        }
        return $this->view->fetch('video');
    }

    //电子教材
    public function textbook()
    {
        $cate = new cateModel();
        $data = $cate->cateTree();
        if($data){
            $this->view->assign('CateList', $data);
        }

        $id = Request::param('id');
        $text = textModel::all(function ($query) use ($id){
            $query->where('cid','=',$id);
        });
        if(!is_null($text)){
            $this->view->assign('text',$text);
        }
        return $this->view->fetch('textbook');
    }
}