<?php
/**
 * Created by Joe.
 * Regist.php
 * 文件描述
 * Create on: 2018/8/27 18:03
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Emailactive as EmailModel;
use app\common\model\User as UserModel;
use think\facade\Session;

class Regist extends Base
{
//注册
    public function index()
    {
        if (Session::has('flag')){ //防止页面刷新重新发送邮件
            if (Session::has('user_id')) {
                $user_id = Session::get('user_id');

                //当前unix时间戳作为注册时间
                $createTime = time();
                //注册默认设置没有激活账户
                $active = 0;
                //激活码为user_id与reg_time的md5串
                $active_code = md5($user_id + $createTime);
                $email = new EmailModel();
                $email->user_id = $user_id;
                $email->create_time = $createTime;
                $email->active = $active;
                $email->active_code = $active_code;

                if ($email->save()) {
                    $user_id = Session::get('user_id');
                    //收件人邮箱
                    $toemail = Usermodel::where('id', $user_id)->value('email');
                    //发件人昵称
                    $name = '单片机教学网';
                    //邮件标题
                    $subject = '请激活您的账户';
                    //邮件内容
                    $content = "<a href='http://www.singlechipedu.com:8080/admin/active/active/activecode/" . $active_code . "'>点击激活</a>";
                    //如果页面打印bool(true)则发送成功
                    if (send_mail($toemail, $name, $subject, $content)) {
                        echo '<body style="text-align: center;color: red;margin-top: 15%;"><h1>请移至邮箱激活您的账号...</h1><h4>（该激活链接于30分钟之后失效，请及时前往邮箱注册）</h4><h5>激活成功<a href="http://www.singlechipedu.com:8080/admin/user/login.html">点击此处</a>返回...</h5></body>';
                    }
                }
            }
            Session::clear();
        }else{
            echo '<body><h5><a href="http://www.singlechipedu.com:8080/admin/site/index.html">点击此处</a>返回...</h5></body>';
        }

    }
}