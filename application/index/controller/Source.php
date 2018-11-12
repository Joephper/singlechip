<?php
/**
 * Created by Joe.
 * Source.php
 * 文件描述
 * Create on: 2018/8/18 11:30
 */

namespace app\index\controller;

use app\common\model\Classes as ClassModel;
use app\common\model\Source as SourceModel;
use app\common\Controller\base;
use think\facade\Request;

class Source extends base
{
    //渲染资源下载界面
    public function Download()
    {
        $classList = ClassModel::all();
        //模板赋值
        if ($classList) {
            $this->view->assign('classList', $classList);
        }
        $classId = Request::param('id');
        if (!empty($classId)) {
            $sourcelist = SourceModel::where('cid', $classId)->paginate(8);
            $this->view->assign('sourcelist', $sourcelist);
            return $this->view->fetch('download');
        }
        $sourcelist = SourceModel::paginate(8);
        $this->view->assign('sourcelist', $sourcelist);

        return $this->view->fetch('download');
    }

    //实现资源下载功能
    public function sourcedownload()
    {
        $this->isLogin();
        //获取主键
        $id = Request::param('id');
        $data = SourceModel::where('id', $id)->value('path');//获取上传至本地服务器的文件路径
        $path = 'uploads/' . $data;//定义文件存放的路径

        //下载四步骤
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header("Content-Length: " . filesize($path));
        readfile($path);
        $pv = SourceModel::where('id', $id)->value('pv');
        $pv = $pv + 1;
        $source = SourceModel::where('id', $id)->find();
        $source->pv = $pv;
        $source->save();
    }
}