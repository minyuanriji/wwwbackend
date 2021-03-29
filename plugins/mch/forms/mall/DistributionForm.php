<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchDistributionDetail;

class DistributionForm extends BaseModel{

    public $mch_id;
    public $share_type;
    public $distribution_level_list;

    public function rules(){
        return [
            [['mch_id', 'share_type'], 'integer'],
            [['distribution_level_list'], 'safe']
        ];
    }

    public function save(){
        try {

            if (!is_array($this->distribution_level_list)) {
                throw new \Exception("参数错误");
            }

            $mch = Mch::findOne($this->mch_id);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            $mch->distribution_detail_set = 1;
            $mch->distribution_share_type = $this->share_type;
            if(!$mch->save()){
                throw new \Exception($this->responseErrorMsg($mch));
            }

            $newList = [];
            $distributionDetailModels = MchDistributionDetail::find()->where(['mch_id' => $this->mch_id])->all();
            if($distributionDetailModels){
                foreach ($distributionDetailModels as $item) {
                    $newList[$item->level] = $item;
                }
            }

            foreach ($this->distribution_level_list as $i => $distributionLevel) {
                if (!isset($newList[$distributionLevel['level']])) {
                    $distributionDetail = new MchDistributionDetail();
                    $distributionDetail->mch_id     = $this->mch_id;
                    $distributionDetail->created_at = time();
                } else {
                    $distributionDetail = $newList[$distributionLevel['level']];
                }
                $distributionDetail->commission_first  = $distributionLevel['commission_first'] ?? 0;
                $distributionDetail->commission_second = $distributionLevel['commission_second'] ?? 0;
                $distributionDetail->commission_third  = $distributionLevel['commission_third'] ?? 0;
                $distributionDetail->level             = $distributionLevel['level'];
                $distributionDetail->updated_at        = time();

                if (!$distributionDetail->save()) {
                    throw new \Exception($this->responseErrorMsg($distributionDetail));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}