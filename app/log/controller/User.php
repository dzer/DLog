<?php

namespace app\log\controller;

use app\log\model\UserModel;
use app\log\service\UserFormService;
use Mll\Common\Common;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

/**
 * 用户
 *
 * @package app\log\controller
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class User extends Controller
{
    public function beforeAction()
    {
        parent::beforeAction();
        if (in_array(Mll::app()->request->getAction(), ['setting', 'member', 'add', 'update', 'delete'])) {
            if (!isset($_SESSION['userInfo']['email'])) {
                return $this->redirect('/log/User/login'. '?callback=' . urlencode(Mll::app()->request->getUrl()));
            }
        }
        if (in_array(Mll::app()->request->getAction(), ['member', 'add', 'update', 'delete'])) {
            if (!isset($_SESSION['userInfo']['role']) || $_SESSION['userInfo']['role'] != 'admin') {
                throw new \Exception('没有权限');
            }
        }
        return true;
    }

    public function login()
    {
        $errorMsg = '';
        if (Mll::app()->request->getIsPost()) {
            $username = Mll::app()->request->post('user');
            $password = Mll::app()->request->post('password');
            $callback = Mll::app()->request->get('callback');
            if (empty($username) || empty($password)) {
                $errorMsg = '账号密码不能为空';
            } else {
                $model = new UserModel();
                if ($model->checkLogin($username, $password)) {
                    if (!empty($callback)) {
                        return $this->redirect($callback);
                    }
                    return $this->redirect('/log/Index/index');
                } else {
                    $errorMsg = '账号或密码不正确';
                }
            }
        }
        return $this->render('login', [
            'errorMsg' => $errorMsg,
        ]);
    }

    public function member()
    {
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 40, 'intval');

        $db = 'system_log';
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('user');
        $sort = 'createTime';
        $where = ['email' => ['$ne' => $_SESSION['userInfo']['email']]];
        $count = $mongo->count($where);

        //计算分页
        $page_count = ceil($count / $page_size);
        $members = Common::objectToArray(
            $collection->find($where, [$sort => -1], ($page - 1) * $page_size, $page_size)
        );

        return $this->render('member', [
            'members' => $members,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
                'count' => $count
            ],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }


    public function add()
    {
        $errorMsg = '';
        if (Mll::app()->request->getIsPost()) {
            $user = new UserFormService();
            if ($user->validate() && $user->insert()) {
                return $this->redirect('/log/User/member');
            } else {
                $errorMsg = $user->getErrorMsg();
            }
        }
        return $this->render('add', [
            'errorMsg' => $errorMsg,
        ]);
    }

    public function update()
    {
        $email = Mll::app()->request->get('email');
        $errorMsg = '';
        $mongo = new Mongo();
        $info = Common::objectToArray($mongo->selectCollection('user')->find(['email' => $email]));
        if (empty($info[0])) {
            throw new \Exception('用户不存在');
        }
        if (Mll::app()->request->getIsPost()) {
            $user = new UserFormService();
            if ($user->validate() && $user->update($email)) {
                return $this->redirect('/log/User/member');
            } else {
                $errorMsg = $user->getErrorMsg();
            }
        }
        return $this->render('update', [
            'errorMsg' => $errorMsg,
            'info' => $info[0]
        ]);
    }

    public function setting()
    {
        $email = isset($_SESSION['userInfo']['email']) ? $_SESSION['userInfo']['email'] : '';
        $errorMsg = $successMsg = '';
        $mongo = new Mongo();
        $info = Common::objectToArray($mongo->selectCollection('user')->find(['email' => $email]));
        if (empty($info[0])) {
            throw new \Exception('用户不存在');
        }
        if (Mll::app()->request->getIsPost()) {
            $user = new UserFormService();
            Mll::app()->request->get(['email' => $email]);
            if ($user->validate() && $user->update($email)) {
                $successMsg = '修改成功';
                if (!empty(Mll::app()->request->post('password'))) {
                    return $this->redirect('/log/User/logout');
                }
            } else {
                $errorMsg = $user->getErrorMsg();
            }
        }
        return $this->render('setting', [
            'errorMsg' => $errorMsg,
            'successMsg' => $successMsg,
            'info' => $info[0]
        ]);
    }

    public function delete()
    {
        $email = Mll::app()->request->get('email');
        if (!empty($email)) {
            $model = new UserModel();
            $model->delete($email);
        }
        return $this->redirect('/log/User/member');
    }


    public function logout()
    {
        @session_unset();
        $sessionId = session_id();
        @session_destroy();
        @session_id($sessionId);

        return $this->redirect('/log/User/login');
    }
}