<?php
namespace Home\Model;

use Think\Model;

class MemberModel extends Model
{
    protected $patchValidate = true; //开启批量验证
    protected $_validate     = [
        // array(验证字段1, 验证规则, 错误提示, [验证条件, 附加规则, 验证时间])
        ['email', 'require', '邮箱必填', Model::EXISTS_VALIDATE, '', Model::MODEL_BOTH],
        ['email', 'email', '邮箱格式不正确', Model::EXISTS_VALIDATE, '', Model::MODEL_BOTH],
        ['email', '', '邮箱不能重复', Model::EXISTS_VALIDATE, 'unique', Model::MODEL_BOTH],

        ['telephone', 'require', '手机号必填', Model::EXISTS_VALIDATE, '', Model::MODEL_BOTH],
        ['telephone', '/^13[23456789]{1}[\d]{8}/', '手机号码需为11位有效号码', Model::EXISTS_VALIDATE, 'regex', Model::MODEL_BOTH],

        ['name', 'require', '用户名不能为空', Model::EXISTS_VALIDATE, '', Model::MODEL_BOTH],
        ['name', '4,32', '用户名长度需为4-32个字符', Model::EXISTS_VALIDATE, 'length', Model::MODEL_BOTH],
        ['name', '', '用户名已存在', Model::EXISTS_VALIDATE, 'unique', Model::MODEL_BOTH],

        ['password', 'require', '请输入密码', Model::EXISTS_VALIDATE, '', Model::MODEL_BOTH],
        ['password', '6,32', '密码长度虚伪6-32位字符', Model::EXISTS_VALIDATE, 'length', Model::MODEL_BOTH],
        ['password', '/^[\w_!@#$%^&*()]+$/', '密码仅由数字字母及_!@#$%^&*()符号组成', Model::EXISTS_VALIDATE, 'regex', Model::MODEL_BOTH],
        ['password', 'checkPasswordStrong', '密码必须要包含字母，数字，特殊符号中两种或两种一种上', Model::EXISTS_VALIDATE, 'callback', Model::MODEL_BOTH],
        ['password', 'confirm', '两次密码输入不一致', Model::EXISTS_VALIDATE, 'confirm', Model::MODEL_BOTH],

        ['is_newsletter', '0,1', '订阅信息必须选择', Model::EXISTS_VALIDATE, 'in', Model::MODEL_BOTH],

        ['agree', 'require', '请阅读隐私政策', Model::MUST_VALIDATE, '', 'register'],
        ['agree', '1', '请同意隐私政策', Model::MUST_VALIDATE, 'equal', 'register'],
    ];
    protected $_auto = [
        // array(验证字段1, 完成规则, [完成时间, 附加规则])
        ['password', 'md5', Model::MODEL_BOTH, 'function'],
    ];
    /**
     * 密码强度验证函数
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function checkPasswordStrong($password)
    {
        $strong = 0;
        if (preg_match('/\d/', $password)) {
            ++$strong;
        }

        if (preg_match('/[a-z]i/', $password)) {
            ++$strong;
        }

        if (preg_match('/[!@#$%^&*()_]/', $password)) {
            ++$strong;
        }

        if ($strong >= 2) {
            return true;
        } else {
            return false;
        }
    }

}
