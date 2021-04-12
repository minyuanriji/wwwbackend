<?php
namespace app\mch\forms\baopin;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;
use app\plugins\mch\models\Mch;

class BaopinImportForm extends BaseModel{

    public $mch_id;
    public $goods_id_str;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'goods_id_str'], 'required'],
            [['mch_id'], 'integer'],
            [['goods_id_str'], 'string']
        ]);
    }

    public function import(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商家不存在");
            }

            $goodsIdArray = explode(",", $this->goods_id_str);
            foreach($goodsIdArray as $goodsId) {
                $goodsId = (int)$goodsId;

                //只能添加一个
                $exists = BaopinMchGoods::find()->where([
                    "goods_id" => $goodsId,
                    "mch_id"   => $mch->id
                ])->exists();
                if($exists) continue;

                $baopinMchGoods = new BaopinMchGoods([
                    "mall_id"    => $mch->mall_id,
                    "goods_id"   => $goodsId,
                    "mch_id"     => $mch->id,
                    "created_at" => time(),
                    "updated_at" => time()
                ]);
                if(!$baopinMchGoods->save()){
                    throw new \Exception($this->responseErrorMsg($baopinMchGoods));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}