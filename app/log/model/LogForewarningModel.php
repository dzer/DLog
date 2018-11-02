<?php

namespace app\log\model;

use Mll\Model;
use Mll\Cache;
use Mll\Db\Mongo;
use Mll\Common\Common;

class LogForewarningModel extends Model
{
    const LOG_HOUR_COUNT_DB = 'system_log';
    const LOG_HOUR_COUNT_TABLE = 'log_forewarning';

    /**
     * 获取报警信息列表
     *
     * @param array $where
     * @param array $sort
     * @param int $skip
     * @param int $limit
     * @return array
     */
    public function getList(array $where, array $sort, $skip = 0, $limit = 10)
    {
        $mongo = new Mongo();
        $collection = $mongo->setDBName(self::LOG_HOUR_COUNT_DB)
            ->selectCollection(self::LOG_HOUR_COUNT_TABLE);
        return Common::objectToArray($collection->find($where, $sort, $skip, $limit));
    }
}