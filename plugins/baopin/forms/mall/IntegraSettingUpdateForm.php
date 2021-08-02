<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;

class IntegraSettingUpdateForm extends BaseModel{

    public $goods_id;

    public $is_permanent;
    public $expire;
    public $integral_num;
    public $period;

    public $enable_integral;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id', 'enable_integral', 'is_permanent', 'expire', 'integral_num', 'period'], 'required']
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            $goods = Goods::findOne($this->goods_id);
            if(!$goods){
                throw new \Exception("商品不存在");
            }

            $integralSetting['expire']       = max((int)$this->expire, 0);
            $integralSetting['integral_num'] = max((int)$this->integral_num, 0);
            $integralSetting['period']       = max((int)$this->period, 0);
            $integralSetting['expire']       = -1;

            $goods->integral_setting = json_encode($integralSetting);
            $goods->enable_integral  = $this->enable_integral ? 1 : 0;

            if(!$goods->save()){
                throw new \Exception($this->responseErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}