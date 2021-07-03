<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\mch\forms\api\apply\LawyerInfoForm;
use app\mch\forms\api\apply\MerchantTypeForm;
use app\mch\forms\api\apply\QueryStatusForm;
use app\mch\forms\api\apply\SettleInfoForm;
use app\plugins\mch\models\Mch;

class MchApplyMoreController extends ApiController{

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

    /**
     * 查询入驻进度
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionQueryStatus(){
        $form = new QueryStatusForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        $this->asJson($form->query());
    }

    /**
     * 修改入驻状态
     */
    public function actionUpStatus(){
        $form = new QueryStatusForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        $this->asJson($form->upQuery());
    }

    /**
     * 保存商户类型
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionMerchantType(){
        $form = new MerchantTypeForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        $this->asJson($form->save());
    }

    /**
     * 保存法人信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionLawyerInfo(){
        $form = new LawyerInfoForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        $this->asJson($form->save());
    }

    /**
     * 结算信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSettleInfo(){
        $form = new SettleInfoForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        $this->asJson($form->save());
    }

}