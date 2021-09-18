<?php
namespace app\component\jobs;


use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\APICacheHelper;
use app\models\Mall;
use yii\base\Component;
use yii\queue\JobInterface;

/**
 * @property ICacheForm $cacheForm
 */
class APICacheJob extends Component implements JobInterface
{
    public $cacheForm;
    public $mall_id;

    public function execute($queue)
    {
        \Yii::$app->mall = Mall::findOne($this->mall_id);
        $cacheObject = \Yii::$app->getCache();
        $cacheKey = APICacheHelper::generateCacheKey($this->cacheForm);
        $dataForm = $this->cacheForm->getSourceDataForm();
        if($dataForm instanceof APICacheDataForm){
            echo "CacheKey:{$cacheKey}\n";
            $cacheObject->set($cacheKey, $dataForm->sourceData, $dataForm->duration);
        }
    }
}