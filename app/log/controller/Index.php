<?php

namespace app\log\controller;

use app\log\model\LogCountHourModel;
use app\log\model\LogModel;
use app\log\service\LogService;
use Mll\Cache;
use Mll\Common\Common;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

class Index extends Controller
{
    const TIME_HOUR = 1;

    const TIME_DAY = 2;

    const YEAR_MONTH_COUNT = 12;

    const SYSTEM_LOG = 'system_log';

    static $filter = array('type','_id');
    
    public function beforeAction()
    {
        parent::beforeAction();
        if (!isset($_SESSION['userInfo']['email'])) {
            return $this->redirect('/log/User/login'. '?callback=' . urlencode(Mll::app()->request->getUrl()));
        }
        return true;
    }

    public function index()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type');
        $project = Mll::app()->request->get('project', 'all');

        $_GET['curr_time'] = $curr_time;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;

        $where = [];
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($curr_time)) {
            $where['date'] = $curr_time;
        }
        Cache::cut('file');
        $expire = 60;
        if ($curr_time < date('Y-m-d')) {
            $expire = 0;
        }
        //今日以前的日志总量
        $cache_key = 'log_count_' . date('d');
        $count = Cache::get($cache_key);
        $logCountModel = new LogCountHourModel();

        if (isset($_GET['count']) || $count === false) {
            $count = $logCountModel->sumField('system_log', '$count', ['date' => ['$lt' => date('Y-m-d')], null]);
            Cache::set($cache_key, $count, 0);
        }

        //今日日志量
        $today_count = $logCountModel->sumField('system_log', '$count', ['date' => ['$gte' => date('Y-m-d')], null]);

        $count += $today_count;
        $model = new LogModel();
        //统计状态
        $status_rs = $logCountModel->countStatus($where, $expire, $curr_time);
        //时间段统计
        $countData = $logCountModel->countByHour($where, $expire, $curr_time);
        //统计错误数
        $count_error_rs = $logCountModel->countError($where, $expire, $curr_time);
        //统计
        $rs = $logCountModel->countByType($where, $expire, $curr_time);

        return $this->render('index2', [
            'countData' => $countData,
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'statusData' => isset($status_rs[0]['result'][0]) ? $status_rs[0]['result'][0] : null,
            'count' => intval($count),
            'today_count' => $today_count,
            'count_error' => isset($count_error_rs[0]['result']) ? $count_error_rs[0]['result'] : [],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'projects' => $model->getProjects(),
            'types' => array_merge($model->types, ['USER' => 'PC请求']),
            'servers' => $model->servers,
        ]);
    }

    /**
     * 最近访问
     */
    public function just()
    {
        $project = Mll::app()->request->get('project', 'all');
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d H:00:00', time() - 3600));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_level = Mll::app()->request->get('log_level');
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $responseCode = Mll::app()->request->get('responseCode');
        $request_id = Mll::app()->request->get('request_id');
        $execTime = Mll::app()->request->get('execTime');
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 40, 'intval');
        $sort = Mll::app()->request->get('sort', 'time');
        $server = Mll::app()->request->get('server');
        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;
        $_GET['sort'] = $sort;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;

        if (empty($sort)) {
            $sort = 'time';
        }
        if ($sort != 'time') {
            $sort = 'content.' . $sort;
        }
        $where = [];
        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if ($log_type == 'USER') {
            $where['type'] = LOG_TYPE_FINISH;
        }
        if (!empty($request_id)) {
            $where['requestId'] = $request_id;
        }
        if (!empty($server)) {
            $where['server'] = $server;
        }
        if (!empty($execTime)) {
            switch ($execTime) {
                case '200':
                    $where['content.execTime']['$lte'] = 0.2;
                    break;
                case '500':
                    $where['content.execTime']['$gt'] = 0.2;
                    $where['content.execTime']['$lte'] = 0.5;
                    break;
                case '1000':
                    $where['content.execTime']['$gt'] = 0.5;
                    break;
                case '1000+':
                    $where['content.execTime']['$gt'] = 1;
                    break;
            }
        }
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }

        if (!empty($request_url)) {
            $where['content.url']['$regex'] = preg_quote(trim($request_url));
        }
        if (is_numeric($responseCode)) {
            if ($responseCode > 0) {
                $where['content.responseCode']['$gte'] = intval($responseCode);
                $where['content.responseCode']['$lte'] = intval($responseCode) + 20;
            } else {
                $where['content.responseCode']['$eq'] = intval($responseCode);
            }
        }
        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('log');
        $model = new LogModel();
        //echo json_encode($where);
        //$count = $mongo->count($where);

        //计算分页
        //$page_count = ceil($count / $page_size);
        $rs = Common::objectToArray($collection->find($where, [$sort => -1], ($page - 1) * $page_size, $page_size));

        return $this->render('just', [
            'rs' => $rs,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                //'page_count' => $page_count,
                //'count' => $count
            ],
            'projects' => $model->getProjects(),
            'types' => $model->types,
            'servers' => $model->servers,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    /**
     * 最近访问
     */
    public function just2()
    {
        $project = Mll::app()->request->get('project', 'all');
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d H:00:00', time() - 1 * 3600));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_level = Mll::app()->request->get('log_level');
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $responseCode = Mll::app()->request->get('responseCode');
        $request_id = Mll::app()->request->get('request_id');
        $execTime = Mll::app()->request->get('execTime');
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 40, 'intval');
        $sort = Mll::app()->request->get('sort', 'time');
        $server = Mll::app()->request->get('server');
        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;
        $_GET['sort'] = $sort;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;

        if (empty($sort)) {
            $sort = 'time';
        }
        if ($sort != 'time') {
            $sort = 'content.' . $sort;
        }
        $where = [];
        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($request_id)) {
            $where['requestId'] = $request_id;
        }
        if (!empty($server)) {
            $where['server'] = $server;
        }
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }
        if (!empty($execTime)) {
            switch ($execTime) {
                case '200':
                    $where['content.execTime']['$lte'] = 0.2;
                    break;
                case '500':
                    $where['content.execTime']['$gt'] = 0.2;
                    $where['content.execTime']['$lte'] = 0.5;
                    break;
                case '1000':
                    $where['content.execTime']['$gt'] = 0.5;
                    break;
                case '1000+':
                    $where['content.execTime']['$gt'] = 1;
                    break;
            }
        }
        if (!empty($request_url)) {
            $where['content.url']['$regex'] = preg_quote(trim($request_url));
        }
        if (is_numeric($responseCode)) {
            if ($responseCode > 0) {
                $where['content.responseCode']['$gte'] = intval($responseCode);
                $where['content.responseCode']['$lte'] = intval($responseCode) + 20;
            } else {
                $where['content.responseCode']['$eq'] = $responseCode;
            }
        }

        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('log');
        $model = new LogModel();
        //echo json_encode($where);
        $count = $mongo->count($where);

        //计算分页
        $page_count = ceil($count / $page_size);
        $rs = Common::objectToArray($collection->find($where, [$sort => -1], ($page - 1) * $page_size, $page_size));

        return $this->render('just2', [
            'rs' => $rs,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
                'count' => $count
            ],
            'projects' => $model->getProjects(),
            'types' => $model->types,
            'servers' => $model->servers,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    /**
     * 日志跟踪
     */
    public function trace()
    {
        $requestId = Mll::app()->request->get('request_id');
        $time = Mll::app()->request->get('time', date('Y-m-d'));

        $db = 'system_log_' . date('m_d', strtotime($time));
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('log');
        $rs = $collection->find(['requestId' => $requestId]);
        $rs = Common::objectToArray($rs);

        if (empty($rs)) {
            throw new \Exception('跟踪日志不存在');
        }
        $xhprof_dir = ROOT_PATH . '/runtime/xhprof' . DS . date('Ymd');
        if (!is_dir($xhprof_dir)) {
            @mkdir($xhprof_dir, 0777, true);
        }
        //traceId排序
        $rs = LogService::traceLogVersionSort($rs);
        $mainRequest = reset($rs);
        if (!isset($_SESSION['userInfo']['role'])
            || ($_SESSION['userInfo']['role'] != 'admin' && $_SESSION['userInfo']['role'] != 'manager')
        ) {
            foreach ($rs as $k => $_rs) {
                $rs[$k]['content']['requestParams'] = '';
            }
        }

        return $this->render('trace', [
            'info' => json_encode($rs),
            'rs' => $rs,
            'xhprof_dir' => $xhprof_dir,
            'main' => $mainRequest
        ]);
    }

    /**
     * 性能排行
     */
    public function rank()
    {
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00'));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $log_level = Mll::app()->request->get('log_level');
        $page = Mll::app()->request->get('page/d', 1, 'intval');
        $page_size = Mll::app()->request->get('limit/d', 20, 'intval');
        $project = Mll::app()->request->get('project', 'help');
        $execTime = Mll::app()->request->get('execTime/f', 5, 'floatval');
        $sort = Mll::app()->request->get('sort', 'count');

        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;
        $_GET['log_type'] = $log_type;
        $_GET['log_level'] = $log_level;
        $_GET['project'] = $project;
        $_GET['execTime'] = $execTime;
        $_GET['sort'] = $sort;

        $where = [];
        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        } else {
            $where['type'] = LOG_TYPE_FINISH;
        }
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }
        if (!empty($request_url)) {
            $where['content.url']['$regex'] = preg_quote(trim($request_url));
        }
        if (empty($sort)) {
            $sort = 'count';
        }

        $where['content.execTime']['$gt'] = $execTime;

        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('log');
        $count = $collection->count($where);
        if (!empty($request_url)) {
            unset($where['content.url']);
            $where['url']['$regex'] = preg_quote(trim($request_url));
        }
        unset($where['content.execTime']);

        $where['execTime']['$gt'] = $execTime;
        //计算分页
        $page_count = ceil($count / $page_size);

        $model = new LogModel();
        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $rs = Common::objectToArray($model->countRank($db, $where, $page, $page_size, $sort));

        return $this->render('rank', [
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
                'count' => $count
            ],
            'projects' => $model->getProjects(),
            'types' => $model->types,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    /**
     * 性能排行
     */
    public function rank2()
    {
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00'));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $log_level = Mll::app()->request->get('log_level');
        $page = Mll::app()->request->get('page/d', 1, 'intval');
        $page_size = Mll::app()->request->get('limit/d', 20, 'intval');
        $project = Mll::app()->request->get('project', 'help');
        $execTime = Mll::app()->request->get('execTime/f', 5, 'floatval');
        $sort = Mll::app()->request->get('sort', 'count');

        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;
        $_GET['log_type'] = $log_type;
        $_GET['log_level'] = $log_level;
        $_GET['project'] = $project;
        $_GET['execTime'] = $execTime;
        $_GET['sort'] = $sort;
        $_GET['request_url'] = $request_url;

        $where = [];

        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        } else {
            $where['type'] = LOG_TYPE_FINISH;
        }
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }
        if (!empty($request_url)) {
            $where['url']['$regex'] = preg_quote(trim($request_url));
        }
        if (empty($sort)) {
            $sort = 'count';
        }

        $where['content.execTime']['$gt'] = $execTime;

        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $mongo = new Mongo();
        $collection = $mongo->setDBName($db)->selectCollection('log');
        $count = $collection->count($where);
        unset($where['content.execTime']);

        $where['execTime']['$gt'] = $execTime;
        //计算分页
        $page_count = ceil($count / $page_size);

        $model = new LogModel();
        $db = 'system_log_' . date('m_d', strtotime($start_time));
        $rs = Common::objectToArray($model->countRank2($db, $where, $page, $page_size, $sort));

        return $this->render('rank2', [
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
                'count' => $count
            ],
            'projects' => $model->getProjects(),
            'types' => $model->types,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    public function count()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type');
        $project = Mll::app()->request->get('project', 'all');

        $_GET['curr_time'] = $curr_time;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;

        $where = [];
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($project) && $project != 'all') {
            $where['project'] = $project;
        }
        if (!empty($curr_time)) {
            $where['date'] = $curr_time;
        }
        Cache::cut('file');
        $expire = 60;
        if ($curr_time < date('Y-m-d')) {
            $expire = 0;
        }

        $logCountModel = new LogCountHourModel();
        $model = new LogModel();
        //类型统计
        $rs = $logCountModel->countByType($where, $expire, $curr_time);
        $db = 'system_log_';

        return $this->render('count', [
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'types' => array_merge($model->types, ['USER' => '用户请求']),
            'projects' => $model->getProjects(),
            'servers' => $model->servers,
        ]);
    }

    public function statisticsBak()
    {
        $time_y = Mll::app()->request->get('time-year', '');
        $time_m = Mll::app()->request->get('time-month', '');
        $time_d = Mll::app()->request->get('time-day', '');
        $project = Mll::app()->request->get('project', '');
        $log_type = Mll::app()->request->get('log_type', '');

        $where = $ret = [];
        if (!empty($project) && 'all' != $project) {
            $where['project'] = $project;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!$time_y && !$time_m && !$time_d) {
            $time_y = date('Y');
            $time_m = date('m');
            $time_d = date('d');
        }

        $logCountModel = new LogCountHourModel();
        switch (true) {
            case $time_y && !$time_m && !$time_d: //月
                $s = '^' . $time_y;
                $where['date'] = new \MongoDB\BSON\Regex($s);
                $result = $logCountModel->statistics([
                    'w' => $where,
                    'g' => [
                        'type' => '$type',
                        'date' => ['$substr' => ['$date', 5, 2]],
                    ],
                    's' => ['_id.date' => 1]
                ]);
                $ret = $this->s_month($result);
                break;
            case $time_y && $time_m && !$time_d: //天
                $s = '^' . $time_y . '-' . $time_m;
                $where['date'] = new \MongoDB\BSON\Regex($s);
                $result = $logCountModel->statistics([
                    'w' => $where,
                    'g' => [
                        'type' => '$type',
                        'date' => '$date',
                    ],
                    's' => ['_id.date' => 1]
                ]);
                $ret = $this->s_day($result, $time_y . '-' . $time_m);
                break;
            case $time_y && $time_m && $time_d: //小时
                $where['date'] = $time_y . '-' . $time_m . '-' . $time_d;
                $result = $logCountModel->statistics([
                    'w' => $where,
                    'g' => [
                        'type' => '$type',
                        'hour' => '$hour',
                    ],
                    's' => ['_id.hour' => 1]
                ]);
                $ret = $this->s_hour($result);
                break;
            default:
                ;
        }
        $model = new LogModel();
        $db = 'system_log';

        return $this->render('statisticsBak', [
            'data' => $ret['data'],
            'lenData' => $ret['lenData'],
            'x' => $ret['x'],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'types' => array_merge($model->types, ['USER' => '用户请求']),
            'projects' => $model->getProjects(),
            'servers' => $model->servers,
            '_g' => $_GET
        ]);
    }

    private function s_month($result)
    {
        $info = [];
        $initArr = [];
        foreach ($result as $key => $val) {
            foreach ($val as $k => $v) {
                if (in_array($k, self::$filter)) continue;
                if (!isset($initArr[$k . $val['type']])) {
                    $info[$k][$val['type']] = self::Tpl(self::YEAR_MONTH_COUNT, 1);
                    $initArr[$k . $val['type']] = 1;
                }
                if ('execTime' == $k && $val['count']) {
                    $v = $v / $val['count'] * 1000;
                    $v = sprintf("%.2f", $v);
                }
                $info[$k][$val['_id']['type']][intval($val['_id']['date'])] = $v;
            }
        }
        unset($result);

        return $this->buildEsData($info, range(1, self::YEAR_MONTH_COUNT));
    }

    private function s_day($result, $m)
    {
        $info = [];
        $initArr = [];
        foreach ($result as $key => $val) {
            foreach ($val as $k => $v) {
                if (in_array($k, self::$filter)) continue;
                if (!isset($initArr[$k . $val['type']])) {
                    $info[$k][$val['type']] = self::Tpl(self::dayInMonth($m), 1);
                    $initArr[$k . $val['type']] = 1;
                }
                if ('execTime' == $k && $val['count']) {
                    $v = $v / $val['count'] * 1000;
                }
                $v = sprintf("%.2f", $v);
                $info[$k][$val['type']][intval(date('d', strtotime($val['_id']['date'])))] = $v;
            }
        }
        unset($result);

        return $this->buildEsData($info, self::fullDay($m));
    }

    private function s_hour($result)
    {
        $info = [];
        $initArr = [];
        foreach ($result as $key => $val) {
            foreach ($val as $k => $v) {
                if (in_array($k, self::$filter)) continue;
                if (!isset($initArr[$k . $val['type']])) {
                    $info[$k][$val['type']] = self::tpl();
                    $initArr[$k . $val['type']] = 1;
                }
                if ('execTime' == $k && $val['count']) {
                    $v = $v / $val['count'] * 1000;
                }
                $v = sprintf("%.2f", $v);
                $info[$k][$val['type']][intval($val['_id']['hour'])] = $v;
            }
        }
        unset($result);

        return $this->buildEsData($info, self::fullHour());
    }

    private function buildEsData($info, $x)
    {
        $data = $lenData = [];
        foreach ($info as $key => $val) {
            foreach ($val as $k => $v) {
                if(!$k)continue;
                $per = [
                    'name' => $k,
                    'type' => 'line',
                    'data' => array_values($v),
                ];
                $data[$key][] = $per;
            }
            $lenData[$key] = array_values(array_filter(array_keys($val)));
        }
        unset($info);

        return [
            'data' => $data,
            'lenData' => $lenData,
            'x' => $x
        ];
    }

    static function fullDay($m)
    {
        return range(1, self::dayInMonth($m));
    }

    static function dayInMonth($m)
    {
        return date('t', strtotime($m));
    }

    static function fullHour()
    {
        return range(0, 23);
    }

    static function tpl($len = 24, $start = 0, $val = 0)
    {
        return array_fill($start, $len, $val);
    }

    public function statistics(){
        $time_y = Mll::app()->request->get('time-year', date('Y'));
        $project = Mll::app()->request->get('project', '');
        $log_type = Mll::app()->request->get('log_type', '');

        $where = $ret = [];
        if (!empty($project) && 'all' != $project) {
            $where['project'] = $project;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        $logCountModel = new LogCountHourModel();
        $s = '^' . $time_y;
        $where['date'] = new \MongoDB\BSON\Regex($s);
        $result = $logCountModel->statistics([
            'w' => $where,
            'g' => [
                'type' => '$type',
                'date' => '$date',
                'hour' => '$hour',
            ],
            's' => ['date'=>1,'_id.hour' => 1]
        ]);

        $data = [];
        foreach($result as $key=>$val){
            $data[$val['type']]['time'][] = $val['date'].' '.$val['_id']['hour'].':00';
            $data[$val['type']]['count'][] = $val['count'];
            $data[$val['type']]['error'][] = $val['error'];
        }
        $model = new LogModel();

        return $this->render('statistics', [
            'data' => $data,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'types' => array_merge($model->types, ['USER' => '用户请求']),
            'projects' => $model->getProjects(self::SYSTEM_LOG),
            'servers' => $model->servers,
            '_g' => $_GET
        ]);
    }
}