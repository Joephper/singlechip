<?php
/**
 * Created by Joe.
 * Send.php
 * 发送邮件
 * Create on: 2018/8/30 20:06
 */

namespace app\admin\controller;

use app\admin\common\Controller\Base;
use app\common\model\Code as CodeModel;
use app\common\model\User as UserModel;
use think\facade\Session;

class Send extends Base
{
    public function index()
    {
        if (Session::has('flag')) { //防止页面刷新重新发送邮件
            if (Session::has('user_id')) {
                $user_id = Session::get('user_id');
                $code_num = get_rand_str(6);
                $code_number =sha1($code_num);
                //当前unix时间戳作为注册时间
                $createTime = time();

                //激活码为user_id与reg_time的md5串

                $code = new CodeModel();
                $code->user_id = $user_id;
                $code->code = $code_number;
                $code->create_time = $createTime;

                if ($code->save()) {
                    $user_id = Session::get('user_id');
                    //收件人邮箱
                    $toemail = Usermodel::where('id', $user_id)->value('email');
                    //发件人昵称
                    $name = '单片机教学网';
                    //邮件标题
                    $subject = '校验码';
                    //邮件内容
                    $content = "<p>您好，您的校验码是" . $code_num . "</p>";
                    //如果页面打印bool(true)则发送成功
                    if (send_mail($toemail, $name, $subject, $content)) {

                    }
                }
            }
            Session::delete('flag');
        }

    }
}
