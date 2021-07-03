<?php
namespace app\mch\controllers;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\mch\forms\mch\MchCashForm;
use app\plugins\mch\models\Mch;

class WithdrawalDetailsController extends ApiController
{
    protected $mch_id;

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
                'user_id'   => (int)\Yii::$app->user->id,
                'is_delete' => 0
            ])->with(["store"])->asArray()->one();
            if(!$mchData){
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_NOT_LOGIN,
                    'msg' => '商户不存在。',
                ];
                return false;
            }
            $this->mch_id  = $mchData['id'];
        }

        return $beforeAction;
    }

    //提现明细
    public function actionMchAllList ()
    {
        $form = new MchCashForm();
        $mch_id = $this->mch_id;
        return $form->getList($mch_id);
    }
}
