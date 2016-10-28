<?php
namespace Home\Controller;

use Think\Controller;
use Think\Verify;

class MemberController extends Controller
{
    /**
     * 会员注册方法
     * @return [type] [description]
     */
    public function registerAction()
    {
        if (IS_POST) {
            // 获取表单提交的数据，插入到数据表中
            $m_member = D('Member'); // 获取Member表操作模型
            if (!$m_member->create($_POST, 'register')) {
                // 数据创建失败，给出提示
                $this->error(implode($m_member->getError(), '<br>'), U('/register'));
            }
            $m_member->add(); // 存储
            //save()
            $this->success('注册成功', U('/center'));
        } else {
            // 展示注册表单
            $this->display();
        }
    }
    /**
     * 会员登陆方法
     * @return [type] [description]
     */
    public function loginACtion()
    {
        if (IS_POST) {
            $t_verify = new Verify;
            if (!$t_verify->check(I('post.verify'))) {
                $this->error('验证码有误!', U('/login'));
            }
            $email    = I('post.email');
            $password = I('post.password');
            //获取对象模型
            $m_member = M('Member');
            //校验用户是否存在
            $condf['email']     = $email;
            $condf['telephone'] = $email;
            $condf['_logic']    = 'OR';
            $row                = $m_member->where($condf)->find();
            if (!$row) {
                $this->error('账号不存在', U('/login'));
            }
            //校验密码
            if ($row['password'] != md5($password)) {
                $this->error('账户或密码错误,请重新登陆', U('/login'));
            }
            unset($row['password']);
            session('member', $row);
            $this->redirect('/center', [], 0);
        } else {
            $this->display();
        }
    }
    /**
     * 验证码生成方法
     * @return [type] [description]
     */
    public function verifyAction()
    {
        $t_verify           = new Verify;
        $t_verify->imageH   = 34;
        $t_verify->imageW   = 140;
        $t_verify->fontSize = 18;
        $t_verify->length   = 4;
        $t_verify->entry();
    }
    /**
     * 会员退出方法
     * @return [type] [description]
     */
    public function logoutAction(){
        session('member', null);
        $this->redirect('/login', [], 0);
    }
}
