<?php
namespace app\plugins\hotel\jobs;


use app\core\ApiCode;
use app\models\Mall;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use yii\base\BaseObject;
use yii\queue\JobInterface;

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
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            //只有最新的一次相同查询作为有效热点查询
            $foundData = $this->form->getFoundData($res['data']['search_id']);
            if($foundData['newest_prepare_id'] == $res['data']['prepare_id']) {
                if ($res['data']['finished'] != 1) {
                    echo "CONTINUE:" . $res['data']['prepare_id'] . "\n";
                    $form = new HotelSearchFilterForm([
                        "prepare_id" => $res['data']['prepare_id']
                    ]);
                    \Yii::$app->queue->delay(0)->push(new HotelSearchFilterJob([
                        "mall_id" => \Yii::$app->mall->id,
                        "form" => $form
                    ]));
                } else {
                    echo "FINISHED:" . $res['data']['prepare_id'] . "\n";
                }
            }
        }catch (\Exception $e){
            echo $e->getMessage() . "\n";
        }
    }
}