<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\component\jobs;

use app\models\ActionLog;
use yii\base\Component;
use yii\queue\JobInterface;

class AdminActionJob extends Component implements JobInterface
{
    public $newBeforeUpdate;
    public $newAfterUpdate;
    public $modelName;
    public $modelId;
    public $remark;
    public $operator;
    public $mall_id;
    public $from;

    public function execute($queue)
    {
        try {
            $form = new ActionLog();
            $form->mall_id = $this->mall_id;
            $form->operator = $this->operator;
            $form->model_id = $this->modelId;
            $form->model = $this->modelName;
            $form->before_update = \Yii::$app->serializer->encode($this->newBeforeUpdate);
            $form->after_update = \Yii::$app->serializer->encode($this->newAfterUpdate);
            $form->remark = $this->remark ?: '数据更新';
            $form->from = empty($this->from) ? 1 : $this->from;
            $res = $form->save();
            \Yii::warning('操作日志存储成功,日志ID:' . $form->id);
            return $res;
        } catch (\Exception $e) {
            \Yii::error('操作日志存储失败,日志ID:' . $form->id);
            \Yii::error($e->getMessage());
        }
    }
}
