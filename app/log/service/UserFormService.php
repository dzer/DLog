<?php

namespace app\log\service;

use app\log\model\UserModel;
use Mll\Db\Mongo;
use Mll\Mll;

class UserFormService
{
    private $errorMsg;

    private $data = [];

    private $is_validate = false;

    public function validate($type = 'insert')
    {
        $data = [];
        $data['email'] = Mll::app()->request->post('email');
        if (!empty(Mll::app()->request->get('email'))) {
            $data['email'] = Mll::app()->request->get('email');
            $type = 'save';
        }

        $data['password'] = Mll::app()->request->post('password');
        $data['repeat_password'] = Mll::app()->request->post('repeat_password');
        $data['phone'] = Mll::app()->request->post('phone', '', 'trim');
        $data['role'] = Mll::app()->request->post('role');

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $this->errorMsg = '邮箱不正确';
            return false;
        }
        if (!preg_match('/@meilele\.com$/', $data['email'])) {
            $this->errorMsg = '非美乐乐企业邮箱';
            return false;
        }
        if ($type == 'insert' && empty($data['password'])) {
            $this->errorMsg = '密码不能为空';
            return false;
        }
        if (!empty($data['password']) && mb_strlen($data['password'], 'utf-8') < 6) {
            $this->errorMsg = '密码不能小于6位';
            return false;
        }
        if ($data['password'] !== $data['repeat_password']) {
            $this->errorMsg = '两次密码输入不一致';
            return false;
        }
        if (!empty($data['phone']) && !preg_match('/^1(3[0-9]|4[579]|5[012356789]|8[0-9]|7[0135678])\d{8}$/', $data['phone'])) {
            $this->errorMsg = '手机号不正确';
            return false;
        }
        if (!empty($data['role']) && !in_array($data['role'], UserModel::ROLE)) {
            $this->errorMsg = '角色不正确';
            return false;
        }
        if (empty($data['role'])) {
            unset($data['role']);
        }
        $mongo = new Mongo();
        if ($type == 'insert' && $mongo->setDBName('system_log')->selectCollection('user')->count(['email' => $data['email']]) > 0) {
            $this->errorMsg = '邮箱已存在';
            return false;
        }
        unset($data['repeat_password']);
        $this->data = $data;
        $this->is_validate = true;
        return true;
    }

    public function insert()
    {
        if (!empty($this->data) && $this->is_validate) {
            $userModel = new UserModel();
            $this->data['password'] = $userModel->generatePasswordHash($this->data['password']);
            $this->data['status'] = UserModel::STATUS['active'];
            $this->data['createTime'] = time();
            if ($userModel->insert($this->data)) {
                return true;
            }
        }
        $this->errorMsg = '保存失败';
        return false;
    }

    public function update($email)
    {
        if (!empty($this->data) && $this->is_validate) {
            $userModel = new UserModel();
            if (!empty($this->data['password'])) {
                $this->data['password'] = $userModel->generatePasswordHash($this->data['password']);
            } else {
                unset($this->data['password']);
            }
            $this->data['updateTime'] = time();
            unset($this->data['email']);
            if ($userModel->update($email, $this->data)) {
                return true;
            }
        }
        $this->errorMsg = '保存失败';
        return false;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

}