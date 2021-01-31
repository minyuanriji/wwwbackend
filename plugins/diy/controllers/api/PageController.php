<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 17:04
 */

namespace app\plugins\diy\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\behaviors\LoginFilter;
use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\forms\api\InfoForm;
use app\models\Goods;
use app\forms\common\goods\GoodsMember;

class PageController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => \app\controllers\behavior\LoginFilter::class,
                'safeActions' => ['detail']
            ]
        ]);
    }

    public function actionDetail($id)
    {
        $page = DiyPage::find()->select('id,title,show_navs')
            ->where([
                'id' => $id,
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
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $page,
        ];
    }

    public function actionStore()
    {
        $form = new InfoForm();
        $form->form_data = json_decode(\Yii::$app->request->post('form_data'), true);
        return $this->asJson($form->save());
    }
}
