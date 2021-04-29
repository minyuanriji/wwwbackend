<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-处理分销的公共订单
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;

use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\CommonOrderGoods;

class CommonOrderGoodsForm extends BaseModel
{
    /** @var array 表单数据 */
    public $form_data;

    public function rules()
    {

    }

    /**
     * 添加
     * @return bool
     * @throws \Exception
     */
    public function addCommonOrderGoods(){
        $trans = \Yii::$app->db->beginTransaction();
        try{
            $commonOrderModel = new CommonOrderGoods();
            $commonOrderModel->mall_id = \Yii::$app->mall->id;
            $commonOrderModel->user_id = \Yii::$app->user->id;
            $commonOrderModel->order_id = $this->form_data["order_id"];
            $commonOrderModel->from_type = CommonOrderGoods::FROM_TYPE_MALL;
            $commonOrderModel->price = $this->form_data["price"];
            $commonOrderModel->goods_id = $this->form_data["goods_id"];
            $commonOrderModel->attr_id = $this->form_data["goods_attr"]["id"];
            $common_order_id = $commonOrderModel->save();
            if($common_order_id === false){
                throw new \Exception("添加失败");
            }
            $trans->commit();
            return true;
        }catch (\Exception $ex){
            $trans->rollBack();
            throw new \Exception(CommonLogic::getExceptionMessage($ex));
        }
    }

    /**
     * 更新订单
     * @param $updateData
     * @param $columns
     * @return bool
     */
    public static function updateCommonOrderGoods($updateData,$columns){
        try{
            $result = CommonOrder::edit($updateData,$columns);
            if($result === false){
                throw new \Exception("更新失败");
            }
            return true;
        }catch (\Exception $ex){
            return false;
        }
    }

    /**
     * 添加公共订单详情
     * @param $common_order_id
     * @return bool
     * @throws \Exception
     */
    public function addCommonOrderDetail($common_order_id){
        foreach ($this->form_data["goods_list"] as $goodsItem){
            $commonOrderDetailModel = new CommonOrderDetail();
            $commonOrderDetailModel->order_id = $this->form_data["order_id"];
            $commonOrderDetailModel->user_id = \Yii::$app->user->id;
            $commonOrderDetailModel->mall_id = \Yii::$app->mall->id;
            $commonOrderDetailModel->num = $goodsItem["num"];
            $commonOrderDetailModel->common_order_id = intval($common_order_id);
            $commonOrderDetailModel->price = $goodsItem['total_price'];;
            $commonOrderDetailModel->goods_id = $goodsItem["id"];
            $result = $commonOrderDetailModel->save();
            if($result === false){
                return false;
            }
        }
        return true;
    }
}
