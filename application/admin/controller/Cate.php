<?php
/**
 * Created by Joe.
 * Cate.php
 * 文件描述
 * Create on: 2018/8/8 16:34
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Cate as cateModel;
use think\Db;
use think\facade\Request;

class Cate extends Base
{
    protected $beforeActionList = [
        'delsoncate' => ['only' => 'del'],
    ];

    public function lst()
    {
        //用户是否登录
        $this->isLogin();
        //实现搜索功能
        $keywords = Request::param('keywords');
        if (!empty($keywords)) {
            $map = ['name', 'like', '%' . $keywords . '%'];
            $lst = cateModel::whereOr([ $map ])
                ->paginate(7);
            $this->view->assign('title','章节管理');
            $this->view->assign('lst',$lst);
            return $this->view->fetch('lst');
        }


        $lst = cateModel::paginate(7);

        $this->view->assign('title','章节管理');
        $this->view->assign('lst',$lst);
        return $this->view->fetch('lst');
    }

    public function add()
    {
        //用户是否登录
        $this->isLogin();
        if (Request()->isPost()) {
            $data = input('post.');
            $res = $this->validate($data, 'app\common\Validate\Cate');
            if (true !== $res) {
                echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
            }else{
                if (db('cate')->insert($data)) {
                    $this->success('新增章节成功', 'cate/lst');
                } else {
                    $this->error('新增章节失败', 'cate/lst');
                }
            }
        }
        $cate = new cateModel();
        $datas = $cate->cateTree();
        $this->view->assign('title','添加章节');
        $this->assign('list', $datas);
        return view();
    }

    public function edit()
    {
        //用户是否登录
        $this->isLogin();
        if (Request()->isPost()) {
            $data = input('post.');

            if (db('cate')->where('id', $data['id'])->update($data)) {
                $this->success('修改章节信息成功', 'cate/lst');
            } else {
                $this->error('修改章节信息失败', 'cate/lst');
            }
        }
        $id = input('id');
        $list = db('cate')->where('id', $id)->find();
        $cate = new cateModel();
        $datas = $cate->cateTree();
        $this->view->assign('title','编辑章节');
        $this->assign(array('data' => $list, 'list' => $datas));
        return view();
    }

    public function del($id)
    {
//用户是否登录
        $this->isLogin();
        if (cateModel::destroy($id)) {
            $this->success('删除章节成功', 'cate/lst');
        } else {
            $this->error('删除章节失败', 'cate/lst');
        }
    }
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
        $list = cateModel::where($where)->delete();
        if ($list !== false) {
            $this->success("成功删除{$list}条！", "lst");
        } else {
            $this->error('删除失败！');
        }
    }

    public function delsoncate()
    {
        $id = input('id');
        $cate = new cateModel;
        $arr = $cate->del($id);
        $arr[] = $id;
    }


}