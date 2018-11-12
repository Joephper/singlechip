<?php
use think\Db;
use PHPMailer\PHPMailer;
// 应用公共文件
//过滤公告题目摘要
function getNoticeContent($content)
{
    return strip_tags(html_entity_decode($content));
}
//过滤新闻题目摘要
function getNewsContent($content)
{
    return mb_substr(strip_tags(html_entity_decode($content)),0,30).'...';
}

//获取章节名称
if (!function_exists('getCateName')){
    function getCateName($id){
        return Db::table('cate')->where('id',$id)->value('name');
    }
}
//获取章节名称
if (!function_exists('getClassName')){
    function getClassName($id){
        return Db::table('class')->where('id',$id)->value('name');
    }
}
/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new PHPMailer\PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = "smtp.163.com"; // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = "w1214586475@163.com";    // SMTP服务器用户名
    $mail->Password = "singlechipkey66";     // SMTP服务器密码
    $mail->SetFrom('w1214586475@163.com', '单片机教学网');
    $replyEmail = '';                   //留空则为发件人EMAIL
    $replyName = '';                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}
//随机字符串
function get_rand_str($len){
    $str = "1234567890asdfghjklqwertyuiopzxcvbnmASDFGHJKLZXCVBNMPOIUYTREWQ";
    return substr(str_shuffle($str),0,$len);
}
