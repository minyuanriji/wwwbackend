<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchDistributionDetail;

class DistributionDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['id'], 'required'],
            [['id'], 'integer']
        ]);
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $distributionLevelArray = [
            [
                'label' => '一级分销',
                'value' => 'commission_first',
            ],
            [
                'label' => '二级分销',
                'value' => 'commission_second',
            ],
            [
                'label' => '三级分销',
                'value' => 'commission_third',
            ],
        ];

        $mch = Mch::findOne($this->id);
        if(!$mch){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => 'error'];
        }

        $distributionDetails = MchDistributionDetail::find()->select([
            'commission_first', 'commission_second', 'commission_third', 'level'
        ])->where(['mch_id' => $this->id])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'shareType'              => (int)$mch->distribution_share_type,
                'distributionLevelArray' => $distributionLevelArray,
                'distributionLevelList'  => DistributionLevelCommon::getInstance()->getList(),
                'distributionDetails'    => $distributionDetails ? $distributionDetails : []
            ]
        ];
    }

}