<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 异常错误日志
 * Author: zal
 * Date: 2020-05-05
 * Time: 14:56
 */

namespace app\logic;

use app\models\ExceptionLog;

class ExceptionLogLogic
{
    /**
     * 异常等级
     */
    const LEVEL_ERROR = 1;// 错误
    const LEVEL_WARNING = 2;// 警告
    const LEVEL_INFO = 3;// 记录信息

    public static function index($page)
    {
        $query = ExceptionLog::find();

        $count = $query->count();
        $pagination = new BasePagination(['totalCount' => $count, 'pageSize' => 20, 'page' => $page - 1]);

        $list = $query->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    public function detail($id)
    {
        $log = ExceptionLog::findOne($id);

        return $log;
    }

    /**
     * 创建异常日志
     * @param $title
     * @param array $content
     * @param $level
     * @return bool
     */
    public function create($title, array $content, $level)
    {
        try {
            $mallId = \Yii::$app->mall->id;
        } catch (\Exception $e) {
            $mallId = 0;
        }

        try {
            $log = new ExceptionLog();
            $log->mall_id = $mallId;
            $log->level = $level;
            $log->title = $title;
            $log->content = \Yii::$app->serializer->encode($content);
            $res = $log->save();
            \Yii::info('异常日志记录是否存储成功:' . $res);
            return $res;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * 错误
     * @param $title
     * @param array $content
     * @return bool
     */
    public function error($title, array $content)
    {
        return $this->create($title, $content, self::LEVEL_ERROR);
    }

    /**
     * 警告
     * @param $title
     * @param array $content
     * @return bool
     */
    public function warning($title, array $content)
    {
        return $this->create($title, $content, self::LEVEL_WARNING);
    }

    /**
     * 信息
     * @param $title
     * @param array $content
     * @return bool
     */
    public function info($title, array $content)
    {
        return $this->create($title, $content, self::LEVEL_INFO);
    }

    /**
     * 删除日志
     * @param $id
     * @return mixed
     */
    public static function delete($id)
    {
        $log = ExceptionLog::findOne($id);

        if ($log) {
            $log->is_delete = 1;

            return $log->save();
        }

        return false;
    }
}
