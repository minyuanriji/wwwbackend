<?php
namespace app\plugins\diy\forms\api;


use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\forms\common\goods\GoodsMember;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\plugins\diy\models\DiyPage;

class DivPageDetailForm extends BaseModel implements ICacheForm{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getSourceDataForm(){
        $page = DiyPage::find()->select('id,title,show_navs')
            ->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_disable' => 0,
                'is_delete' => 0,
            ])->with(['navs' => function ($query) {
                $query->select('id,name,page_id,template_id')->with(['template' => function ($query) {
                    $query->select('id,name,data');
                }]);
            }])->asArray()->one();
        if (!$page) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '页面不存在。',
            ];
        }
        if (!empty($page['navs'])) {
            foreach ($page['navs'] as &$nav) {
                if (!empty($nav['template']['data'])) {
                    $component_list = SerializeHelper::decode($nav['template']['data']);
                    foreach($component_list as $i => $component){
                        if ($component['id'] == 'goods' ){
                            $goods_list = $component['data']['list'];
                            $showGoodsLevelPrice = isset($component['data']['showGoodsLevelPrice']) ? $component['data']['showGoodsLevelPrice'] : 0;
                            if(isset($showGoodsLevelPrice) && $showGoodsLevelPrice){
                                $goodsMemberInfo = new GoodsMember();
                                $goodsMemberInfo->is_login = $this->is_login;
                                $goodsMemberInfo->login_uid = $this->login_uid;

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
                }
                $nav['template']['data'] = $component_list;
            }
        }

        $sourceData = [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $page,
        ];

        return new APICacheDataForm([
            "sourceData" => $sourceData
        ]);
    }

    public function getCacheKey(){
        $keys[] = (int)\Yii::$app->mall->id;
        $keys[] = (int)$this->id;
        return $keys;
    }
}