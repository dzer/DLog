<?php

namespace app\log\service;

use Mll\Mll;

class ForewarningService
{
    public static $msg = [];

    /**
     * 报警条件
     * @var array
     */
    private static $forewarningConfig = [];

    public static function start()
    {
        self::$msg = [];
        self::$forewarningConfig = Mll::app()->config->params('forewarning');
        if (!empty(self::$forewarningConfig)) {
            foreach (self::$forewarningConfig as &$config) {
                if (empty($config['action']) || empty($config['enable'])) {
                    break;
                }
                $functionArr = explode('::', $config['action']);
                list($class, $function) = $functionArr;
                $class = 'app\\log\\service\\' . $class;
                try {
                    $msg = (new $class())->$function($config);
                    $config['rs'] = $msg;
                    if (is_array($msg) && !empty($msg)) {
                        if (isset($msg[0])) {
                            self::$msg = array_merge(self::$msg, $msg);
                        } else {
                            self::$msg[] = $msg;
                        }
                    }
                } catch (\Exception $e) {
                    Mll::app()->log->warning('预警调用方法' . $config['action'] . '错误，' . $e->getMessage());
                }
            }
        }
        return self::$forewarningConfig;
    }

    /**
     * 发送消息
     *
     * @return array
     */
    public static function sendMsg()
    {
        $rs = [];
        if (!empty(self::$msg)) {
            foreach (self::$msg as $k => $msg) {
                foreach ($msg['sendType'] as $type => $user) {
                    if ($type == 'wechat') {
                        $rs[$k][$type] = self::sendWechat($msg['msg'], $user);
                    }
                }
            }
        }
        return $rs;
    }

    /**
     * 发送微信消息
     *
     * @param string $msg 消息
     * @param array|string $user 用户账号
     * @return array
     */
    public static function sendWechat($msg, $user)
    {
        $rs = [];
        $url = Mll::app()->config->params('wechat_url');
        $user = is_string($user) ? array($user) : $user;
        if (is_array($user)) {
            foreach ($user as $_user) {
                if ($_user == 'all') {
                    $rs[$_user] = Mll::app()->curl->get($url . '?content=' . urlencode($msg));
                } else {
                    $rs[$_user] = Mll::app()->curl->get($url . '?content=' . urlencode($msg) . '&wxzh=' . $_user);
                }
            }
        }
        return $rs;
    }
}