<?php
/**
 * User: zhouwang
 */

namespace app\log\controller;

use app\log\model\LogCountHourModel;
use app\log\model\LogModel;
use app\log\service\LogService;
use Mll\Cache;
use Mll\Common\Common;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;


class History extends Controller
{
    public function beforeAction()
    {
        parent::beforeAction();
        if (!isset($_SESSION['userInfo']['email'])) {
            return $this->redirect('/log/User/login'. '?callback=' . urlencode(Mll::app()->request->getUrl()));
        }
        return true;
    }
    /**
     * 最近访问
     */
    public function just()
    {
        if(php_uname('n') != '56e217629648'){
            return $this->render('tips', [
                'base_url' => '/' . Mll::app()->request->getModule()
                    . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
            ]);
        }
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

        $mongoConfig = Mll::app()->config->get('db.mongo');
        $db = 'system_log_' . date('y_m_d', strtotime($start_time)).'_online';
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        $collection = $mongo->selectCollection('log');
        $model = new LogModel();
        //echo json_encode($where);
        //$count = $mongo->count($where);

        //计算分页
        //$page_count = ceil($count / $page_size);
        $rs = Common::objectToArray($collection->find($where, [$sort => -1], ($page - 1) * $page_size, $page_size));
        //最早时间
        $ret = Common::objectToArray($mongo->setDBName('admin')->executeCommand(['listDatabases' => true]));
        $dbList = isset($ret[0]['databases']) ? $ret[0]['databases'] : [];
        //system_log_11_09_online
        $earliest = '';
        if($dbList){
            foreach($dbList as $k=>$v){
                if(false !== strpos($v['name'],'online')){
                    $earliest = str_replace(['system_log_','_online','_'],['','','-'],$v['name']);
                    break;
                }
            }
        }

        return $this->render('just', [
            'rs' => $rs,
            'earliest' => '(from:'.$earliest.')',
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                //'page_count' => $page_count,
                //'count' => $count
            ],
            'projects' => $model->getProjects($db),
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

        $mongoConfig = Mll::app()->config->get('db.mongo');
        $db = 'system_log_' . date('m_d', strtotime($time)).'_online';
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
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
}