<?php
namespace app\plugins\hotel\jobs;

use app\models\Mall;
use yii\base\BaseObject;
use yii\queue\JobInterface;


class HotelSearchPrepareJob extends BaseObject implements JobInterface{

    public $mall_id;
    public $prepareForm;

    public function execute($queue){

    }
}