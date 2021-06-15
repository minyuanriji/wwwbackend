<?php
namespace app\forms\api;


use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\GoodsMember;
use app\helpers\SerializeHelper;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\HomePage;

class CacheIndexForm extends BaseModel implements ICacheForm{

    public $mall_id;

    public function rules(){
        return [
            [['mall_id'], 'integer']
        ];
    }

    public function getSourceDataForm(){

        $homePgae = HomePage::findOne(['mall_id' => $this->mall_id, 'is_delete' => 0]);

        if (!$homePgae) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '请在后台装修首页');
        }

        $component_list = SerializeHelper::decode($homePgae->page_data);
        foreach ($component_list as $i => $component) {
            if ($component['id'] == 'label-bar') {
                $label_list = $component['data']['label_list'];
                foreach ($label_list as &$label) {
                    $query = Goods::find()
                        ->alias('g')
                        ->with(['goodsWarehouse', 'attr'])
                        ->where(['g.is_delete' => 0, 'g.is_recycle' => 0, 'g.status' => 1, 'g.mall_id' => $this->mall_id,])
                        ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

                    if ($label['title']) {
                        $query->keyword($label['title'], [
                            'or',
                            ['like', 'gw.name', $label['title']],
                            ['like', 'g.labels', $label['title']]]);
                    }

                    /**
                     * @var BasePagination $pagination
                     */
                    $list = $query->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                        ->groupBy('g.goods_warehouse_id')
                        ->page($pagination, 2, 1)
                        ->all();
                    $newList = [];
                    /* @var Goods[] $list */
                    foreach ($list as $item) {
                        $apiGoods = ApiGoods::getCommon();
                        $apiGoods->goods = $item;
                        $apiGoods->isSales = 0;
                        $detail = $apiGoods->getDetail();
                        $detail['app_share_title'] = $item->app_share_title;
                        $detail['app_share_pic'] = $item->app_share_pic;
                        $detail['use_attr'] = $item->use_attr;
                        $detail['unit'] = $item->unit;
                        if ($item->use_virtual_sales) {
                            $detail['sales'] = $item->sales + $item->virtual_sales;
                        }
                        $newList[] = $detail;
                    }
                    $label['goods_list'] = $newList;
                }
                $component_list[$i]['data']['label_list'] = $label_list;
                break;
            }
            if ($component['id'] == 'goods' ){
                $goods_list = $component['data']['list'];
                $showGoodsLevelPrice = isset($component['data']['showGoodsLevelPrice']) ? $component['data']['showGoodsLevelPrice'] : 0;
                if(isset($showGoodsLevelPrice) && $showGoodsLevelPrice){
                    $goodsMemberInfo = new GoodsMember();
                    $goodsMemberInfo->login_uid = $this->login_uid;
                    $goodsMemberInfo->is_login = $this->is_login;
                    foreach ($goods_list as $k=>$goods) {
                        $goodsInfo = Goods::find()->where(['=','id',$goods['id']])->one();
                        $level_price = $goodsMemberInfo->getGoodsMemberPrice($goodsInfo);
                        if($goods['price']>$level_price){
                            $goods_list[$k]['level_price'] = $level_price;
                        }else{
                            $goods_list[$k]['level_price'] = $goods['price'];
                        }
                    }
                    $component_list[$i]['data']['list'] = $goods_list;
                }
            }
        }
        $data = [];
        $app_share_title = $app_share_pic = $app_share_desc = "";
        $mallSettings = AppConfigLogic::getMallSettingConfig(["app_share_title","app_share_pic","app_share_desc"]);
        if(!empty($mallSettings)){
            $app_share_title = isset($mallSettings["app_share_title"]) ? $mallSettings["app_share_title"] : "";
            $app_share_pic = isset($mallSettings["app_share_pic"]) ? $mallSettings["app_share_pic"] : "";
            $app_share_desc = isset($mallSettings["app_share_desc"]) ? $mallSettings["app_share_desc"] : "";
        }
        $data["app_share_title"] = $app_share_title;
        $data["app_share_pic"] = $app_share_pic;
        $data["app_share_desc"] = $app_share_desc;

        $sourceData = $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', ['page_data' => $component_list,"share_data" => $data]);

        return new APICacheDataForm([
            "sourceData" => $sourceData
        ]);
    }

    public function getCacheKey(){
        $keys[] = (int)$this->mall_id;
        return $keys;
    }
}