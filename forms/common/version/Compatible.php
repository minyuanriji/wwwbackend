<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 兼容处理
 * Author: zal
 * Date: 2020-04-18
 * Time: 10:50
 */

namespace app\forms\common\version;


use yii\base\BaseObject;

class Compatible extends BaseObject
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param integer|array|string $data
     * @return array
     * 兼容4.1.0之前的发货方式
     */
    public function sendType($data = null)
    {
        if (!$data) {
            $data = ['express', 'offline'];
        } elseif (!is_array($data)) {
            $data = json_decode($data, true);
            if (!is_array($data)) {
                if (is_numeric($data)) {
                    if ($data == 2) {
                        $data = ['offline'];
                    } elseif ($data == 1) {
                        $data = ['express'];
                    } else {
                        $data = ['express', 'offline'];
                    }
                } else {
                    $data = ['express', 'offline'];
                }
            }
        }
        return $data;
    }
}
