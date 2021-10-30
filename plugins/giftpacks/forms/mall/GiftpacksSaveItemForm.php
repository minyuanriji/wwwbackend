<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\mch\forms\goods\GoodsEditForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Store;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\mch\models\Mch;

class GiftpacksSaveItemForm extends BaseModel{

    public $name;
    public $cover_pic;
    public $item_price;
    public $pack_id;
    public $store_id;
    public $goods_id;
    public $goods_price;
    public $expired_at;
    public $max_stock;
    public $usable_times;
    public $limit_time;

    public function rules(){
        return [
            [['name', 'cover_pic', 'item_price', 'pack_id', 'store_id',
              'goods_id', 'goods_price', 'expired_at', 'max_stock', 'usable_times'], 'required'],
            [['limit_time'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $store = Store::findOne($this->store_id);
            if(!$store || $store->is_delete){
                throw new \Exception("门店[ID:{$this->store_id}]不存在");
            }

            $mch = Mch::findOne($store->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户{$store->mch_id}不存在");
            }

            if(!$this->goods_id){ //添加商品
                $newGoodsData = $this->newGoodsData();
                $newGoodsForm = new GoodsEditForm();
                $newGoodsForm->attributes  = $newGoodsData;
                $newGoodsForm->attrGroups  = [];
                $newGoodsForm->expressName = [];
                $newGoodsForm->mch_id      = $mch->id;
                $res = $newGoodsForm->save();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                $goods = Goods::findOne($res['data']['goods_id']);
            }else{
                $goods = Goods::findOne($this->goods_id);
            }

            if(!$goods || $goods->is_delete || $goods->is_recycle){
                throw new \Exception("商品不存在");
            }

            if($goods->mch_id != $mch->id){
                throw new \Exception("商品[ID:{$goods->id}]非商户产品");
            }

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            if($this->max_stock < $giftpacks->max_stock){
                throw new \Exception("商品库存不能低于大礼包库存");
            }

            $uniqueData = [
                "mall_id"  => \Yii::$app->mall->id,
                "pack_id"  => $this->pack_id,
                "store_id" => $this->store_id,
                "goods_id" => $goods->id,
            ];
            $item = GiftpacksItem::findOne($uniqueData);
            if(!$item){
                $item = new GiftpacksItem($uniqueData);
            }

            //总结算价不能大于大礼包价格
            $totalItemPrice = $this->item_price + (float)GiftpacksItem::find()->where([
                "is_delete" => 0,
                "pack_id"   => $giftpacks->id
            ])->andWhere(["NOT IN", "id", [$item ? $item->id : 0]])->sum("item_price");
            if($totalItemPrice > $giftpacks->price){
                throw new \Exception("总结算价不能大于大礼包价：" . $giftpacks->price);
            }

            $item->name         = $this->name;
            $item->cover_pic    = $this->cover_pic;
            $item->item_price   = $this->item_price;
            $item->created_at   = time();
            $item->updated_at   = time();
            $item->expired_at   = !empty($this->expired_at) ? strtotime($this->expired_at) : 0;
            $item->max_stock    = (int)$this->max_stock;
            $item->usable_times = (int)$this->usable_times;
            $item->limit_time   = (int)$this->limit_time;

            if(!$item->save()){
                throw new \Exception($this->responseErrorMsg($item));
            }

            //上面添加商品完之后设置商户商品会把商品下架，此处在上架  后台设置商户上架商品审核
            $goods->status = Goods::STATUS_ON;
            if (!$goods->save())
                throw new \Exception($this->responseErrorMsg($goods));

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 拼装商品数据
     * @return array
     */
    private function newGoodsData(){
        $goodsData = array_merge(BaseGoodsEdit::baseGoodsDataTpl(), [
            "pic_url"   => [["id" => 0, "pic_url" => $this->cover_pic]],
            "cover_pic" => $this->cover_pic,
            "name"      => $this->name,
            "price"     => $this->goods_price,
            "goods_num" => $this->max_stock
        ]);
        return $goodsData;
    }
}