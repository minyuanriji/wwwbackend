<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\plugins\mch\models\Mch;

class MchMApiController extends ApiController{

    protected $mch_id;
    protected $mchData;

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function beforeAction($action){
        $beforeAction = parent::beforeAction($action);
        if($beforeAction){
            $mchData = Mch::find()->where([
                'user_id'       => (int)\Yii::$app->user->id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ])->with(["store"])->asArray()->one();
            if(!$mchData){
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_NOT_LOGIN,
                    'msg' => '商户不存在。',
                ];
                return false;
            }
            $this->mch_id  = $mchData['id'];
            $this->mchData = $mchData;
        }

        return $beforeAction;
    }

    public function init(){
        parent::init();
    }
}