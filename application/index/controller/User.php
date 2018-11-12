<?php
/**
 * Created by Joe.
 * User.php
 * 用户控制器
 * Create on: 2018/7/21 16:45
 */

namespace app\index\controller;

use app\common\Controller\base;
use app\common\model\User as UserModel;
use app\common\model\Emailactive as EmailModel;
use app\common\model\Code as CodeModel;
use think\facade\Request;
use think\facade\Session;
use think\captcha\Captcha;

class User extends base
{
    //处理用户提交的注册信息
    public function insert()
    {
        //检测注册是否关闭
        $this->is_reg();
        if (Request::isAjax()) {
            //验证数据
            $data = Request::post();//要验证的数据
            $rule = 'app\common\validate\User';//自定义的验证规则
            //开始验证
            $res = $this->validate($data, $rule);
            if (true !== $res) {  //false
                return ['status' => -1, 'message' => $res];
            } else {
                if ($user = UserModel::create($data)) {
                    //注册成功后自动登录
                    $result = UserModel::get($user->id);
                    $active = EmailModel::where('user_id', $result->id)->value('active');
                    $flag=0;
                    Session::set('flag',$flag); //标识
                    Session::set('active', $active);
                    Session::set('user_id', $result->id);
                    Session::set('user_name', $result->name);
                    return ['status' => 1];
                } else {
                    return ['status' => 0, 'message' => '注册失败，请检查！'];
                }
            }

        } else {
            $this->error("请求类型错误", 'register');
        }
    }

    //用户登录
    public function login()
    {
        $this->Logined();
        return $this->view->fetch('login', ['title' => '用户登录']);
    }

    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }

    //用户登录验证
    public function loginCheck()
    {
        if (Request::isAjax()) {
            //验证数据
            $data = Request::post();//要验证的数据
            $rule = [
                'email|邮箱' => 'require|email',
                'password|密码' => 'require|alphaNum',
                'verify|验证码' => 'require',
            ];//自定义的验证规则
            //开始验证
            $res = $this->validate($data, $rule);
            if (true !== $res) {  //false
                return ['status' => -1, 'message' => $res];
            } else {
                //执行查询
                $result = UserModel::get(function ($query) use ($data) {
                    $query->where('email', $data['email'])
                        ->where('password', sha1($data['password']));
                });
                $email = UserModel::get(function ($query) use ($data) {
                    $query->where('email', $data['email']);
                });
                $verify = Request::post('verify');
                if (null == $email){
                    return ['status' => 2, 'message' => '账号不存在！'];
                }
                if (null == $result) {
                    return ['status' => 0, 'message' => '邮箱或密码错误，请重新输入！'];
                } else {
                    if (!captcha_check($verify)) {
                        return ['status' => -3, 'message' => '验证码错误！'];
                    } else {
                        //将用户的数据写到session
                        $flag=0;
                        Session::set('flag',$flag); //标识
                        Session::set('user_id', $result->id);
                        Session::set('user_name', $result->name);
                        $active = EmailModel::where('user_id', $result->id)->value('active');
                        Session::set('active', $active);
                        $data['user_id'] = Session::get('user_id');
                        if (UserModel::where('id', $data['user_id'])->value('status') == 0) {
                            Session::clear();
                            return ['status' => -2, 'message' => '该用户已被拉入黑名单'];
                        } else {
                            //账户是否激活
                            if (EmailModel::where('user_id', $data['user_id'])->value('active') == 0) {
                                if(EmailModel::where('user_id', $data['user_id'])->delete())
                                {
                                    return ['status' => -4, 'message' => '该账号未激活！已重新发送激活邮件！请前往邮箱激活！'];
                                }
                            } else {
                                $array = Usermodel::where('id', $data['user_id'])->value('role');
                                Session::set('user_role', $array);
                                return ['status' => 1, 'message' => '登录成功！'];
                            }
                        }
                    }
                }
            }

        } else {
            $this->error("请求类型错误", 'login');
        }
    }

    //注销用户
    public function logout()
    {
//        Session::delete('user_id');
//        Session::delete('user_name');
        Session::clear();
        $this->success('用户已注销', 'index/index');
    }
    //检测邮箱
    public function checkEmail()
    {
        Session::clear();
        if (Request::isAjax()) {
            //验证数据
            $data = Request::post();//要验证的数据
            $rule = 'app\common\validate\Email';//自定义的验证规则
            //开始验证
            $res = $this->validate($data, $rule);
            if (true !== $res) {  //false
                return ['status' => -1, 'message' => $res];
            } else {
                $verify = Request::param('verify');
                $result = Usermodel::where('email', $data['email'])->find();
                if (null == $result) {
                    return ['status' => 0, 'message' => '账号不存在！'];
                } else {
                    if (!captcha_check($verify)) {
                        return ['status' => -2, 'message' => '验证码错误！'];
                    }
                    $user_id = Usermodel::where('email', $data['email'])->value('id');
                    Session::set('user_id', $user_id);
                    return ['status' => 1];
                }
            }
        } else {
            $this->error("请求类型错误", 'login');
        }
    }

    //发送校验码
    public function sendCode()
    {
        if (Session::has('user_id')) {
            $user_id = Session::get('user_id');
            $code_num = get_rand_str(6);
            $code_number = sha1($code_num);
            //当前unix时间戳作为注册时间
            $createTime = time();

            $codemodel = CodeModel::where('user_id', $user_id)->find();
            if (null == $codemodel) {
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
                    $subject = '验证码';
                    //邮件内容
                    $content = "<p>您好，您的验证码是" . $code_num . "</p>";
                    //如果页面打印bool(true)则发送成功
                    if(send_mail($toemail, $name, $subject, $content)){
                        return ['status' => 1];
                    }
                }
            } else {
                $code = CodeModel::where('user_id', $user_id)->find();
                $code->code = $code_number;
                $code->create_time = $createTime;
                if ($code->save()) {
                    $user_id = Session::get('user_id');
                    //收件人邮箱
                    $toemail = Usermodel::where('id', $user_id)->value('email');
                    //发件人昵称
                    $name = '单片机教学网';
                    //邮件标题
                    $subject = '验证码';
                    //邮件内容
                    $content = "<p>您好，您的验证码是" . $code_num . "</p>";
                    //如果页面打印bool(true)则发送成功
                    if(send_mail($toemail, $name, $subject, $content)){
                        return ['status' => 0];
                    }
                }
            }

        }
        else{
            $this->error('没有找到账号','login');
        }
    }

    //检验校验码
    public function checkCode()
    {
        if (Request::isAjax()) {
            $data = Request::post();//要验证的数据
            $rule = 'app\common\validate\Code';//自定义的验证规则
            //开始验证
            $res = $this->validate($data, $rule);
            if (true !== $res) {  //false
                return ['status' => -1, 'message' => $res];
            }else{
                if (Session::has('user_id')) {
                    $user_id = Session::get('user_id');
                    $code=CodeModel::where('user_id',$user_id)->value('code');
                    if ($code==sha1($data['code'])){
                        return ['status' => 1];
                    }else{
                        return ['status' => 0, 'message' => '验证码错误！'];
                    }
                }
            }
        }else{
            $this->error("请求类型错误", 'login');
        }
    }
    //重置密码
    public function resetPSW()
    {
        if (Request::isAjax()) {
            $data = Request::post();//要验证的数据
            $rule = 'app\common\validate\NewPassword';//自定义的验证规则
            //开始验证
            $res = $this->validate($data, $rule);
            if (true !== $res) {  //false
                return ['status' => -1, 'message' => $res];
            }else{
                if (Session::has('user_id')) {
                    $user_id = Session::get('user_id');
                    $user= Usermodel::where('id',$user_id)->find();
                    $user->password=$data['password'];
                    if ($user->save()){
                        return ['status' => 1];
                    }
                }
            }
        }else{
            $this->error("请求类型错误", 'login');
        }
    }
}