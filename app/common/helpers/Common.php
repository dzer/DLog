<?php

namespace app\common\helpers;
class Common
{
    /**
     * 版本对比
     *
     * @param $va
     * @param $vb
     * @return int
     */
    public static function version_compare($va, $vb)
    {
        $a = array_shift($va);
        $b = array_shift($vb);
        if ($a > $b) {
            return 1;
        } elseif ($a < $b) {
            return 0;
        } else {
            return self::version_compare($va, $vb);
        }
    }

    /**
     * 版本号排序
     *
     * @param $versionArr
     * @return mixed
     */
    public static function version_sort($versionArr)
    {
        array_walk($versionArr, function (&$value) {
            $value = explode('.', $value);
        });

        for ($i = 0; $i < count($versionArr) - 1; $i++) {
            for ($j = 0; $j < count($versionArr) - 1 - $i; $j++) {
                if (self::version_compare($versionArr[$j], $versionArr[$j + 1])) {
                    $tmp = $versionArr[$j];
                    $versionArr[$j] = $versionArr[$j + 1];
                    $versionArr[$j + 1] = $tmp;
                }
            }
        }

        array_walk($versionArr, function (&$value) {
            $value = implode('.', $value);
        });

        return $versionArr;
    }

    /**
     * 拼装html select option
     *
     * @param array $arr 数组
     * @param $select
     * @return string
     */
    public static function optionHtml($arr, $select)
    {
        $html = '';
        foreach ($arr as $k => $v) {
            $selectStr = (isset($_GET[$select]) && $_GET[$select] == $k ? 'selected="selected"' : '');
            $html .= '<option ' . $selectStr . ' value="' . $k . '">' . $v . '</option>';
        }
        return $html;
    }
}