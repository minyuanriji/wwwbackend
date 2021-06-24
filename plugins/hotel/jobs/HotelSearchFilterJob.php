<?php
namespace app\plugins\hotel\jobs;


use app\models\Mall;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class HotelSearchFilterJob
 * @package app\plugins\hotel\jobs
 * @property int $mall_id
 * @property HotelSearchFilterForm $form
 */
class HotelSearchFilterJob extends BaseObject implements JobInterface{

    public $mall_id;
    public $form;

    public function execute($queue){
        \Yii::$app->mall = Mall::findOne($this->mall_id);
        try {
            $res = $this->form->filter();
            print_r($res);
            exit;
        }catch (\Exception $e){
            echo $e->getMessage() . "\n";
        }
    }
}