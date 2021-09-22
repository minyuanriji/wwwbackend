<?php
namespace app\helpers;

use app\component\jobs\APICacheJob;
use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use yii\db\ActiveQuery;

class APICacheHelper{

    /**
     * @param ICacheForm $cacheForm
     * @param boolean $forceDb
     * @return array
     */
    public static function get(ICacheForm $cacheForm, $forceDb = false){

        if($cacheForm instanceof BaseModel){
            $cacheForm->base_mall_id = \Yii::$app->mall->id;
            $cacheForm->is_login = !\Yii::$app->user->isGuest;
            $cacheForm->login_uid = $cacheForm->is_login ? \Yii::$app->user->id : 0;
        }

        try {

            $cacheKey = static::generateCacheKey($cacheForm);

            $cacheObject = \Yii::$app->getCache();
            $cacheData = $cacheObject->get($cacheKey);
            if($forceDb || !$cacheData || (defined('ENV') && ENV != "pro")){
                $dataForm = $cacheForm->getSourceDataForm();
                if($dataForm instanceof APICacheDataForm){
                    $cacheObject->set($cacheKey, $dataForm->sourceData, $dataForm->duration);
                    $cacheData = $dataForm->sourceData;
                }else{
                    $cacheData = $dataForm;
                }
            }else{
                \Yii::$app->queue->delay(0)->push(new APICacheJob([
                    "cacheForm" => $cacheForm,
                    "mall_id"   => \Yii::$app->mall->id
                ]));
            }
            return [
                "code" => ApiCode::CODE_SUCCESS,
                "data" => $cacheData
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 生成缓存键
     * @param ICacheForm $cacheForm
     * @return string
     */
    public static function generateCacheKey(ICacheForm $cacheForm){

        $keyArray[] = get_class($cacheForm);
        if($cacheForm instanceof BaseModel){
            $keyArray[] = $cacheForm->base_mall_id;
            if($cacheForm->is_login){
                $keyArray[] = $cacheForm->login_uid;
            }
        }
        $keys = $cacheForm->getCacheKey();
        if(!empty($keys)){
            $keyArray = array_merge($keyArray, is_array($keys) ? $keys : [$keys]);
        }
        return md5(strtolower(implode(":", $keyArray)));
    }
}
