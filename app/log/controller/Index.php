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
    public function __construct()
    {
        if (!Mll::app()->config->params('log_auth', true) || (isset($_GET['admin']) && $_GET['admin'] == '2253dsag23&^') || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
            $_SESSION['admin'] = 1;
        } else {
            exit('没有权限');
        }
    }

    public function index2()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $project = Mll::app()->request->get('project', 'all');
        $_GET['curr_time'] = $curr_time;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;

        $where = [];
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($project)) {
            $where['project'] = $project;
        }
        if (!empty($curr_time)) {
            $where['time']['$gte'] = $curr_time . ' 00:00:00';
            $where['time']['$lte'] = $curr_time . ' 23:59:59';
        }
        Cache::cut('file');
        $expire = 1800;
        if ($curr_time < date('Y-m-d')) {
            $expire = 0;
        }
        //今日以前的日志总量
        $cache_key = 'log_count_' . date('d');
        $count = Cache::get($cache_key);
        $mongo = (new Mongo())->selectCollection('log');
        if (isset($_GET['count']) || $count === false) {
            $count = $mongo->count(['microtime' => ['$lt' => strtotime(date('Y-m-d 0:0:0'))]]);
            Cache::set($cache_key, $count, 0);
        }

        //今日日志量
        $cache_key = 'log_today_count';
        $today_count = Cache::get($cache_key);
        if (isset($_GET['count']) || $today_count === false) {
            $today_count = $mongo->count(['microtime' => ['$gte' => strtotime(date('Y-m-d 0:0:0'))]]);
            Cache::set($cache_key, $today_count, 600);
        }
        $count += $today_count;
        $model = new LogModel();

        //统计状态
        $status_rs = $model->countStatus($where, $expire, $curr_time);
        //时间段统计
        $count_rs = $model->count($where, $expire, $curr_time);
        //统计错误数
        $count_error_rs = $model->countError($where, $expire, $curr_time);

        $countData = array();
        if (!empty($count_rs[0]['result'])) {
            foreach ($count_rs[0]['result'] as $_count) {
                $hour = intval(date('H', strtotime($_count['_id']['date'] . ':00')));
                $time[$hour] = $_count['time'];
                $success[$hour] = $_count['code_200'];
                $fail[$hour] = $_count['count'] - $success[$hour];
                $error[$hour] = $_count['error'];
            }
        }
        for ($i = 0; $i < 24; $i++) {
            $countData['count_time'][] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $countData['time'][$i] = isset($time[$i]) ? floatval(sprintf('%.2f', ($time[$i] * 1000))) : 0;
            $countData['success'][$i] = isset($success[$i]) ? $success[$i] : 0;
            $countData['fail'][$i] = isset($fail[$i]) ? $fail[$i] : 0;
            $countData['error'][$i] = isset($error[$i]) ? $error[$i] : 0;
        }
        return $this->render('index', [
            'countData' => $countData,
            'statusData' => isset($status_rs[0]['result'][0]) ? $status_rs[0]['result'][0] : null,
            'count' => intval($count),

            'today_count' => $today_count,
            'count_error' => isset($count_error_rs[0]['result']) ? $count_error_rs[0]['result'] : [],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'projects' => $model->projects,
            'types' => $model->types,
        ]);
    }

    public function index()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
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
            $count = $logCountModel->sumField('$count', ['date' => ['$lt' => date('Y-m-d')], null]);
            $count = isset($count['count']) ? $count['count'] : 0;
            Cache::set($cache_key, $count, 0);
        }

        //今日日志量
        $today_count = $logCountModel->sumField('$count', ['date' => ['$gte' => date('Y-m-d')], null]);
        $today_count = isset($today_count['count']) ? $today_count['count'] : 0;

        $count += $today_count;
        $model = new LogModel();

        //统计状态
        $status_rs = $logCountModel->countStatus($where, $expire, $curr_time);

        //时间段统计
        $countData = $logCountModel->countByHour($where, $expire, $curr_time);

        //统计错误数
        $count_error_rs = $logCountModel->countError($where, $expire, $curr_time);

        return $this->render('index2', [
            'countData' => $countData,
            'statusData' => isset($status_rs[0]['result'][0]) ? $status_rs[0]['result'][0] : null,
            'count' => intval($count),
            'today_count' => $today_count,
            'count_error' => isset($count_error_rs[0]['result']) ? $count_error_rs[0]['result'] : [],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction(),
            'projects' => $model->projects,
            'types' => $model->types,
            'servers' => $model->servers,
        ]);
    }

    /**
     * 最近访问
     */
    public function just()
    {
        $project = Mll::app()->request->get('project', 'all');
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d H:00:00', time() - 4 * 3600));
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
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($request_id)) {
            $where['requestId'] = $request_id;
        }
        if (is_numeric($responseCode)) {
            if ($responseCode > 0) {
                $where['content.responseCode']['$gte'] = intval($responseCode);
                $where['content.responseCode']['$lte'] = intval($responseCode) + 20;
            } else {
                $where['content.responseCode']['$eq'] = $responseCode;
            }
        }

        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
        $model = new LogModel();
        //echo json_encode($where);
        //$count = $model->countNum($where);

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
            'projects' => $model->projects,
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

        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
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
        if (!isset($_GET['param']) && Mll::app()->config->params('log_param_close', 'true')) {
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
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00'), '-10 day');
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $page = Mll::app()->request->get('page/d', 1, 'intval');
        $page_size = Mll::app()->request->get('limit/d', 20, 'intval');
        $project = Mll::app()->request->get('project', 'help');
        $execTime = Mll::app()->request->get('execTime/f', 1, 'floatval');
        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;
        $_GET['log_type'] = $log_type;
        $_GET['project'] = $project;
        $_GET['execTime'] = $execTime;

        $where = [];
        if (!empty($project)) {
            $where['project'] = $project;
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
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        } else {
            $where['type'] = LOG_TYPE_FINISH;
        }
        $where['content.execTime']['$gt'] = $execTime;

        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
        $count = $collection->count($where);
        unset($where['content.execTime']);

        $where['execTime']['$gt'] = $execTime;
        //计算分页
        $page_count = ceil($count / $page_size);

        $model = new LogModel();
        $rs = Common::objectToArray($model->countRank($where, $page, $page_size));

        return $this->render('rank', [
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
                'count' => $count
            ],
            'projects' => $model->projects,
            'types' => $model->types,
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }
}