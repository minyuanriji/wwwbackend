<?php
namespace app\plugins\hotel\jobs;

use app\core\ApiCode;
use app\helpers\TencentMapHelper;
use app\models\Mall;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchPrepareForm;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class HotelSearchJob
 * @package app\plugins\hotel\jobs
 * @property int $mall_id
 * @property HotelSearchPrepareForm $form
 */
class HotelSearchPrepareJob extends BaseObject implements JobInterface{

    public $mall_id;
    public $form;

    public function execute($queue){
        \Yii::$app->mall = Mall::findOne($this->mall_id);

        try {

            $res = $this->form->prepare();
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $form = new HotelSearchFilterForm([
                "prepare_id" => $res['data']['prepare_id']
            ]);
            \Yii::$app->queue->delay(0)->push(new HotelSearchFilterJob([
                "mall_id" => \Yii::$app->mall->id,
                "form"    => $form
            ]));
        }catch (\Exception $e){
            echo $e->getMessage() . "\n";
        }

    }
}