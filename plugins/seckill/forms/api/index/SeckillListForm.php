<?php

namespace app\plugins\seckill\forms\api\index;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use function Webmozart\Assert\Tests\StaticAnalysis\float;

class SeckillListForm extends BaseModel
{
    public $type;

    public function rules()
    {
        return [
            [['type'], 'string']
        ];
    }

    public function list()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try{
            /*$beforeDawn = strtotime(date('Y-m-d', time()));//凌晨0:0
            $night = strtotime(date('Y-m-d',strtotime('+1 day'))) - 1;//23:59*/

            $seckill = Seckill::find()->andWhere([
                'and',
                ['<', 'start_time', time()],
                ['>', 'end_time', time()],
                ['is_delete' => 0],
                ['mall_id' => \Yii::$app->mall->id],
            ])->with(
                'seckillGoods',
                'seckillGoods.seckillGoodsPrice',
                'seckillGoods.goods',
                'seckillGoods.goods.goodsWarehouse'
            )->select('id,name,start_time,end_time,pic_url')->asArray()->one();

            $result = [];
            if ($seckill) {
                foreach ($seckill['seckillGoods'] as &$item) {
                    $item['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'] ?? '';
                    $item['name'] = $item['goods']['goodsWarehouse']['name'] ?? '';
                    $item['original_price'] = $item['goods']['goodsWarehouse']['original_price'] ?? '';
                    array_multisort(array_column($item['seckillGoodsPrice'],'score_deduction_price'),SORT_ASC,$item['seckillGoodsPrice']);
                    $item['score_deduction_price'] = $item['seckillGoodsPrice'][0]['score_deduction_price'] ?? 0;
                    $item['seckill_price'] = $item['seckillGoodsPrice'][0]['seckill_price'] ?? 0;
                    unset($item['goods'], $item['seckillGoodsPrice']);

                    //获取虚假比例
                    $ratio = ceil($item['virtual_stock'] / $item['real_stock']) ;
                    $rand = rand(ceil($ratio / 10), $ratio);

                    //获取真实购买数
                    $item['buyNum'] = SeckillGoods::SeckillGoodsBuyNum($item['goods_id'], $seckill);
                    if ($item['buyNum']) {
                        $keyArray = [];
                        $keyArray['buyNum'] = $item['buyNum'];
                        $keyArray['falseNum'] = $item['buyNum'] * $rand;

                        $progressKeyID = md5('JFMS' . $seckill['id'] . $item['goods_id']);

                        //过期时间
                        if ($item['buyNum'] == $item['real_stock']) {
                            $item['falseNum'] = $item['virtual_stock'];
                        } else {
                            $cacheTime = $seckill['end_time'] - time() + 1800;
                            $progressNum = $this->getSeckillGoodsProgress($progressKeyID, json_encode($keyArray), $cacheTime);
                            $item['falseNum'] = $progressNum;
                        }
                    } else {
                        $item['falseNum'] = 0;
                    }
                    $surplus = floatval(intval($item['falseNum']) / intval($item['virtual_stock']));
                    $item['surplus_percentage'] = round($surplus, 2);
                    if ($item['surplus_percentage'] <= 0) {
                        $item['surplus_percentage'] = 0.01;
                    }
                }
                $seckill['start_time'] = date('Y-m-d', $seckill['start_time']);
                $seckill['end_time'] = date('Y-m-d H:i:s', $seckill['end_time']);
                $result = $seckill;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '数据请求成功',
                'data' => $result,
            ];

        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 获取秒杀商品进度条
     * @param string $keyID
     * @return array|null
     */
    protected static function getSeckillGoodsProgress($keyID, $keyArray, $cacheTime)
    {
        $cache = \Yii::$app->getCache();
        $progressNum = $cache->get($keyID);
        if(!$progressNum){
            $cache->set($keyID, $keyArray, $cacheTime);
            $valArray = json_decode($keyArray, true);
            $progressNum = $valArray['falseNum'];
        } else {
            if (is_string($progressNum)) {
                $valArray = json_decode($progressNum, true);
            } else {
                $valArray = $progressNum;
            }

            $keyArray = json_decode($keyArray, true);

            if ($keyArray['buyNum'] == $valArray['buyNum']) {
                $progressNum = $valArray['falseNum'];
            }

            if ($keyArray['buyNum'] > $valArray['buyNum']) {
                if ($valArray['falseNum'] > $keyArray['buyNum']) {
                    $valArray['falseNum'] += $keyArray['buyNum'];
                } else {
                    $valArray['falseNum'] = $keyArray['falseNum'];
                }
                $value = json_encode($valArray);
                $cache->set($keyID, $value, $cacheTime);
                $progressNum = $valArray['falseNum'];
            }
        }
        return $progressNum;
    }

}