<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\forms\mall\goods\GoodsEditForm;
use app\models\BaseModel;
use app\plugins\taobao\models\TaobaoAccount;
use app\plugins\taobao\models\TaobaoGoods;

class TaobaoGoodsRemoteImportForm extends BaseModel {

    public $account_id;
    public $cat_ids;
    public $import_list;

    public function rules(){
        return [
            [['cat_ids', 'import_list', 'account_id'], 'required']
        ];
    }

    public function import(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $account = TaobaoAccount::findOne($this->account_id);
            if(!$account || $account->is_delete){
                throw new \Exception("应用账号不存在");
            }

            $goodsIds = [];

            foreach($this->import_list as $importData){

                $goodsData = $this->getDefaultGoodsData();

                //商品价格加上邮费
                $goodsData['price']          = $importData['reserve_price'] + $importData['real_post_fee'];
                $goodsData['original_price'] = $goodsData['price'];

                $goodsData['name']           = $importData['title'];
                $goodsData['goods_num']      = $importData['volume'];
                $goodsData['virtual_sales']  = $importData['tk_total_sales'];
                $goodsData['cover_pic']      = $importData['pict_url'];
                $goodsData['cats']           = $this->cat_ids;
                $goodsData['pic_url']        = [];
                $smallImages = !empty($importData['small_images']) ? $importData['small_images']['string'] : [];
                if($smallImages){
                    if(isset($smallImages['string'])){
                        $goodsData['pic_url'][] = ['id' => 0, 'pic_url' => $smallImages['string']];
                    }else{
                        foreach($smallImages as $imageUrl){
                            $goodsData['pic_url'][] = ['id' => 0, 'pic_url' => $imageUrl];
                        }
                    }
                }

                $form = new GoodsEditForm();
                $form->attributes  = $goodsData;
                $form->attrGroups  = [];
                $form->expressName = [];
                $res = $form->save();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $taobaoGoodsModel = new TaobaoGoods([
                    "mall_id"     => \Yii::$app->mall->id,
                    "goods_id"    => $res['data']['goods_id'],
                    "updated_at"  => time(),
                    "created_at"  => time(),
                    "account_id"  => $account->id,
                    "app_key"     => $account->app_key,
                    "adzone_id"   => $account->adzone_id,
                    "special_id"  => $account->special_id,
                    "invite_code" => $account->invite_code,
                    "url"         => $importData['url'],
                    "num_iid"     => $importData['num_iid'],
                    "coupon_id"   => $importData['coupon_id']
                ]);
                if(!$taobaoGoodsModel->save()){
                    throw new \Exception($this->responseErrorMsg($taobaoGoodsModel));
                }

                $goodsIds[] = $res['data']['goods_id'];
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'goods_id_list' => $goodsIds
                ]
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    private function getDefaultGoodsData(){
        $jsonString = '{"labels":[],"attr":[],"cats":["15"],"mchCats":[],"cards":[],"services":[],"pic_url":[{"id":"30508","pic_url":"http://yingmlife-1302693724.cos.ap-guangzhou.myqcloud.com/uploads/images/original/20211126/20534aec0f5ac2ff9745d36bb5c61d8f.webp"}],"use_attr":0,"goods_num":"9999","status":0,"unit":"件","virtual_sales":0,"cover_pic":"","sort":100,"accumulative":0,"confine_count":-1,"confine_order_count":-1,"forehead":0,"forehead_score":0,"forehead_score_type":1,"freight_id":0,"freight":null,"give_score":0,"give_score_type":1,"individual_share":0,"is_level":1,"is_level_alone":0,"goods_brand":"","goods_supplier":"","pieces":0,"share_type":0,"attr_setting_type":0,"video_url":"","is_sell_well":0,"is_negotiable":0,"name":"商品名称","price":"0","original_price":0,"cost_price":0,"detail":"<p>1</p>","extra":"","app_share_title":"","app_share_pic":"","is_default_services":1,"member_price":{},"goods_no":"","goods_weight":"","select_attr_groups":[],"goodsWarehouse_attrGroups":[],"is_on_site_consumption":0,"share_level_type":0,"distributionLevelList":[],"form":null,"is_show_sales":0,"use_virtual_sales":1,"form_id":0,"attr_default_name":"","is_area_limit":0,"use_score":0,"area_limit":[{"list":[]}],"full_relief_price":0,"fulfil_price":0,"cannotrefund":["1","2","3"],"profit_price":0,"enable_upgrade_user_role":0,"upgrade_user_role_type":"","product":"","purchase_permission":[],"first_buy_setting":{"buy_num":0,"return_red_envelopes":0,"return_commission":0},"lianc_user_id":0,"lianc_commission_type":1,"lianc_commisson_value":0,"enable_score":0,"enable_integral":0,"max_deduct_integral":0,"integral_fee_rate":0,"is_order_paid":0,"is_order_sales":"0","price_display":[{"key":"price","display_id":0}]}';
        $defaultData = json_decode($jsonString, true);
        return $defaultData;
    }
}