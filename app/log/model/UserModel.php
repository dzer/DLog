<?php

namespace app\log\model;

use Mll\Mll;
use Mll\Model;
use Mll\Cache;
use Mll\Db\Mongo;
use Mll\Common\Common;

class UserModel extends Model
{
    const STATUS = [
        'inactive' => 0,
        'active' => 1,
        'delete' => 2
    ];

    const ROLE = [
        'manager' => 'manager',
        'member' => 'member'
    ];

    public function insert($data) {
        $mongo = new Mongo();
        return $mongo->setDBName('system_log')->selectCollection('user')->batchInsert([$data]);
    }

    public function delete($email) {
        $mongo = new Mongo();
        return $mongo->setDBName('system_log')->selectCollection('user')->remove(['email' => $email]);
    }

    public function update($email, $data) {
        if (empty($email) || empty($data)) {
            return false;
        }
        $mongo = new Mongo();
        return $mongo->setDBName('system_log')->selectCollection('user')->update(['email' => $email], ['$set' => $data]);
    }

    public function checkLogin($username, $password)
    {
        $mongo = new Mongo();
        $rs = Common::objectToArray($mongo->setDBName('system_log')->selectCollection('user')->find(['email' => $username, 'status' => self::STATUS['active']]));
        if (!empty($rs[0])) {
            $userInfo = $rs[0];
            if ($this->validatePassword($password, $userInfo['password'])) {
                session_start();
                $_SESSION['userInfo'] = [
                    'userId' => $userInfo['_id']['oid'],
                    'email' => $userInfo['email'],
                    'role' => $userInfo['role'],
                ];
                session_write_close();
                return true;
            }
        }
        return false;
    }


    /**
     * 验证密码
     *
     * @param $password
     * @param $hash
     * @return bool
     */
    public function validatePassword($password, $hash)
    {
        if (!is_string($password) || $password === '') {
            throw new \BadMethodCallException('密码不能为空或不是字符串');
        }

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            throw new \BadMethodCallException('哈希是无效的');
        }
        return password_verify($password, $hash);
    }

    /**
     * 生成密码哈希
     *
     * @param $password
     * @param int $cost
     * @return bool|string
     */
    public function generatePasswordHash($password, $cost = 10)
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
    }

    /**
     * 获取角色
     *
     * @param null $role
     * @return array|mixed
     */
    public static function getRoles($role = null) {
        $roles = [
            'member' => '普通成员',
            'manager' => '管理员',
        ];
        return isset($roles[$role]) ? $roles[$role] : $roles;
    }

}