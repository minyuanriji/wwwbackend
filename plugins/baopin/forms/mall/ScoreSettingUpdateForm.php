<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;

class ScoreSettingUpdateForm extends BaseModel{

    public $goods_id;

    public $is_permanent;
    public $expire;
    public $integral_num;
    public $period;

    public $enable_score;
    public $give_score;
    public $give_score_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id', 'enable_score', 'is_permanent', 'expire',
              'integral_num', 'period', 'give_score', 'give_score_type'], 'required']
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

            $scoreSetting['expire']       = max((int)$this->expire, 0);
            $scoreSetting['integral_num'] = max((int)$this->integral_num, 0);
            $scoreSetting['period']       = max((int)$this->period, 0);
            if($this->is_permanent){ //永久
                $scoreSetting['expire'] = -1;
            }

            $goods->score_setting   = json_encode($scoreSetting);
            $goods->enable_score    = $this->enable_score ? 1 : 0;
            $goods->give_score      = max((int)$this->give_score, 0);
            $goods->give_score_type = in_array((int)$this->give_score_type, [1, 2]) ? (int)$this->give_score_type : 0;

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