<?php
/**
 * Created by Joe.
 * Active.php
 * 邮箱激活
 * Create on: 2018/8/27 19:08
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Emailactive as EmailModel;
use think\facade\Request;

class Active extends Base
{
    public function active()
    {
        $request = Request::instance();
        //此加密串为user_id+reg_time的md5
        //获取邮件请求中的激活加密串
        $active_code = $request->param('activecode');

        if (!($user = EmailModel::get(['active_code' => $active_code]))) {
            echo "此链接已失效！";
            exit();
        } else {
            $create_time=EmailModel::where('active_code',$active_code)->value('create_time');
            $time=time();
            $time_value=$time-$create_time;
            if (($time_value/60) > 30){
                echo "此链接已过期！";
            }
            //检查用户是否已经激活，用户表设置一个字段标识是1否0已经激活
            if ($user->active == 1) {
                echo "该用户已激活！";
                exit;
            }
            //把此加密串对应的user的user_id与reg_time进行md5并与此加密串进行对比
            if ($active_code == $user->active_code) {
                $user->active = 1;
                if ($user->save()) {
                    echo '<body><h5>恭喜您注册成功！</h5></body>';
                }
            }
        }

    }

}
