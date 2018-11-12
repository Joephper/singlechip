<?php
/**
 * Created by Joe.
 * User.php
 * 后台用户控制器
 * Create on: 2018/7/23 11:26
 */

namespace app\admin\controller;

use app\admin\common\controller\Base;
use app\admin\common\model\User as Usermodel;
use app\common\model\Emailactive as EmailModel;
use app\common\model\Code as CodeModel;
use think\facade\Request;
use think\facade\Session;
use think\captcha\Captcha;

class User extends Base
{
    //渲染登录界面
    public function login()
    {
        $this->view->assign('title', '管理员登录');
        return $this->view->fetch('login');
    }

    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }

    //验证后台登录
    public function checkLogin()
    {
        $data = Request::param();
        $rule = [
            'email|邮箱' => 'require|email',
            'password|密码' => 'require|alphaNum',
            'verify|验证码' => 'require',
        ];
        $res = $this->validate($data, $rule);
        if (true !== $res) {  //false
            echo '<script>alert("' . $res . '");window.history.go(-1);</script>';
        } else {
            $map[] = ['email', '=', $data['email']];
            $map[] = ['password', '=', sha1($data['password'])];
            $map[] = ['role', '=', '2'];

            $result = Usermodel::where($map)->find();
            $verify = Request::param('verify');
            if (null == $result) {
                echo "'<script>alert('手机号或密码错误');window.history.go(-1);</script>";
            } else {
                if (!captcha_check($verify)) {
                    echo "'<script>alert('验证码错误');window.history.go(-1);</script>";
                } else {
                    $active = EmailModel::where('user_id', $result->id)->value('active');
                    if ($active == 1) {
                        $flag = 0;
                        Session::set('flag', $flag); //标识
                        Session::set('user_id', $result['id']);
                        Session::set('user_name', $result['name']);
                        Session::set('user_role', $result['role']);

                        Session::set('active', $active);

                        $this->success('登录成功', 'admin/site/index');
                    } else {
                        if (EmailModel::where('user_id', $result->id)->delete()) {
                            echo "'<script>alert('该账号未激活！已重新发送激活邮件！请前往邮箱激活！');window.history.href='Regist/index';</script>";
                        }
                    }

                }
            }
        }
    }

    //退出登录
    public function logout()
    {
        Session::clear();
        $this->success('用户已注销', 'admin/user/login');
    }

    //    添加管理员页面
    public function AddAdmin()
    {
        //用户是否登录
        $this->isLogin();
        $this->assign('title', '添加管理员');
        return $this->fetch();
    }

    //处理用户提交的信息
    public function insert()
    {
        //用户是否登录
        $this->isLogin();

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
                    Session::clear();
                    $result = UserModel::get($user->id);
                    $flag = 0;
                    Session::set('flag', $flag); //标识
                    Session::set('user_id', $result->id);
                    Session::set('user_name', $result->name);
                    $active = EmailModel::where('user_id', $result->id)->value('active');
                    Session::set('active', $active);
                    return ['status' => 1];
                } else {
                    return ['status' => 0, 'message' => '添加失败，请检查！'];
                }
            }

        } else {
            $this->error("请求类型错误", 'AddAdmin');
        }
    }

    //用户列表
    public function userList()
    {
        //用户是否登录
        $this->isLogin();
        //实现搜索功能
        $keywords = Request::param('keywords');
        if (!empty($keywords)) {
            $map1 = ['mobile', 'like', '%' . $keywords . '%'];
            $map2 = ['email', 'like', '%' . $keywords . '%'];
            $map3 = ['realname', 'like', '%' . $keywords . '%'];
            $userList = Usermodel::whereOr([$map1, $map2, $map3])
                ->paginate(7);

            //模板赋值
            $this->view->assign('title', '用户管理');
            $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
            $this->view->assign('userList', $userList);
            //渲染出用户的列表模板
            return $this->view->fetch('userList');
        }

        $userList = Usermodel::paginate(7);

        //模板赋值
        $this->view->assign('title', '用户管理');
        $this->view->assign('empty', '<span style="color: red;;">没有任何数据</span>');
        $this->view->assign('userList', $userList);

        //渲染出用户的列表模板
        return $this->view->fetch('userList');
    }

    //编辑用户
    public function userEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取要更新的用户主键
        $userId = Request::param('id');
        //根据主键进行查询
        $userInfo = Usermodel::where('id', $userId)->find();
        //设置编辑界面的模板变量
        $this->view->assign('title', '修改用户权限');
        $this->view->assign('userInfo', $userInfo);
        //渲染出编辑界面
        return $this->view->fetch('useredit');
    }

    public function adminEdit()
    {
        //用户是否登录
        $this->isLogin();
        //获取当前用户id和权限
        $data['user_id'] = Session::get('user_id');
        //根据主键进行查询
        $userInfo = Usermodel::where('id', $data['user_id'])->find();
        //设置编辑界面的模板变量
        $this->view->assign('title', '个人中心');
        $this->view->assign('userInfo', $userInfo);
        //渲染出编辑界面
        return $this->view->fetch('adminedit');
    }

    //执行编辑用户的保存用户信息操作
    public function saveUser()
    {
        //用户是否登录
        $this->isLogin();
        //获取用户提交的信息
        $data = Request::param();
        //获取主键
        $id = $data['id'];

        //删除主键
        unset($data['id']);

        //执行更新操作
        if (Usermodel::where('id', $id)->data($data)->Update()) {
            return $this->success('更新成功', 'userList');
        }
        $this->error('没有更新或更新失败');
    }

    //修改密码
    public function editPassword()
    {
        //用户是否登录
        $this->isLogin();
        if (Request::isAjax()) {
            $data = Request::param();
            $res = $this->validate($data, 'app\common\Validate\Password');
            if (true !== $res) {
                return ['status' => -1, 'message' => $res];
            } else {
                $data['user_id'] = Session::get('user_id');
                $result = UserModel::get(function ($query) use ($data) {
                    $query->where('id', $data['user_id'])
                        ->where('password', sha1($data['oldpassword']));
                });

                if (null == $result) {
                    return ['status' => 0, 'message' => '旧密码输入错误!'];
                } else {
                    $user = Usermodel::where('id', $data['user_id'])->find();
                    $password = sha1($data['password']);
                    $user->password = $password;
                    $user->save();
                    return ['status' => 1, 'message' => '修改成功!'];
                }
            }
        } else {
            $this->error("请求类型错误", 'adminEdit');
        }

    }

    //删除用户
    public function userDelete()
    {
        //用户是否登录
        $this->isLogin();
        //获取主键
        $id = Request::param('id');


        //执行删除操作
        if (Usermodel::where('id', $id)->delete()) {
            return $this->success('删除成功', 'userlist');
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
        $list = UserModel::where($where)->delete();
        if ($list !== false) {
            $this->success("成功删除{$list}条！", "userlist");
        } else {
            $this->error('删除失败！');
        }
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
            $this->error('没有找到账号');
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
