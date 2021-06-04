<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\CacheHelper;
use app\mch\forms\api\GetMchStoreForm;
use app\models\DistrictArr;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;

class GetMchStoreController extends ApiController {

    /**
     * 获取商家店铺信息
     * @return \yii\web\Response
     */
    public function actionIndex(){
        $detail = CacheHelper::get(CacheHelper::MCH_API_GET_MCH_STORE, function($helper){
            $form = new GetMchStoreForm();
            $form->attributes = $this->requestData;
            return $helper($form->getDetail());
        });
        return $this->asJson($detail);
    }

    //添加浏览记录
    public function addVisit($mch_id, $user_id)
    {
        try {
            $mch = Mch::findOne($mch_id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            if ($mch->user_id == $user_id) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}