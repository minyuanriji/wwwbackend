<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchSubAccount;

class MchMApiController extends ApiController{

    protected $check_auth = true;
    protected $mch_id;
    protected $mchData;
    protected $is_sub = false;

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function beforeAction($action){
        $beforeAction = parent::beforeAction($action);

        $cleanSubHeader = false;
        try {
            if($beforeAction && $this->check_auth){

                $headers = \Yii::$app->request->headers;
                $subMchId = $headers['x-sub-mch-id'];
                $mchData = null;
                if($subMchId){
                    $subAccount = MchSubAccount::findOne([
                        "mch_id"  => $subMchId,
                        "user_id" => (int)\Yii::$app->user->id
                    ]);
                    if(!$subAccount){
                        $cleanSubHeader = true;
                        throw new \Exception("无权限操作");
                    }
                    $mchData = Mch::find()->where([
                        'id'            => $subMchId,
                        'review_status' => Mch::REVIEW_STATUS_CHECKED,
                        'is_delete'     => 0
                    ])->with(["store"])->asArray()->one();
                    $this->is_sub = true;
                }else{
                    $mchData = Mch::find()->where([
                        'user_id'       => (int)\Yii::$app->user->id,
                        'review_status' => Mch::REVIEW_STATUS_CHECKED,
                        'is_delete'     => 0
                    ])->with(["store"])->asArray()->one();
                }

                if(!$mchData){
                    throw new \Exception("商户不存在");
                }

                $this->mch_id  = $mchData['id'];
                $this->mchData = $mchData;

                $this->checkAuth($action);
            }
        }catch (\Exception $e){
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_FAIL,
                'clean_header_sub_mch_id' => $cleanSubHeader ? 1 : 0,
                'msg' => $e->getMessage(),
            ];
            return false;
        }

        return $beforeAction;
    }

    /**
     * 检查权限
     * @param $action
     * @return void
     */
    private function checkAuth($action){
        $denys = [
            "api/account/withdraw",
            "api/mch-set/set-account",
            "sub-account/delete",
            "sub-account/add",
            "api/mch-set/set-info",
            "api/account/set-withdraw-pwd",
            "api/account/set-settle-info",
            "api/mch-bind-mobile/verify"
        ];
        //echo strtolower($this->id . "/" . $action->id);
        //exit;
        if($this->is_sub && in_array(strtolower($this->id . "/" . $action->id), $denys)){
            throw new \Exception("未分配子账号操作权限");
        }
    }

    public function init(){
        parent::init();
    }
}