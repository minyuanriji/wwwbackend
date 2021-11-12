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
                'and',/*
                ['<', 'start_time', time()],*/
                ['>', 'end_time', time()],
                ['is_delete' => 0],
                ['mall_id' => \Yii::$app->mall->id],
            ])->with(
                'seckillGoods',
                'seckillGoods.seckillGoodsPrice',
                'seckillGoods.goods',
                'seckillGoods.goods.goodsWarehouse'
            )->select('id,name,start_time,end_time,pic_url')->orderBy('end_time asc')->asArray()->one();

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
                    $ratio = floor($item['virtual_stock'] / $item['real_stock']);

                    $remainRatio = $ratio - 4;

                    if ($remainRatio <= 0) {
                        $remainRatio = 2;
                    }

                    $rand = mt_rand($remainRatio, $ratio);

                    //获取真实购买数
                    $item['buyNum'] = SeckillGoods::SeckillGoodsBuyNum($item['goods_id'], $seckill);
                    if ($item['buyNum'] > 0) {
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

                    if ($item['falseNum'] == 0) {
                        $item['surplus_percentage'] = 0;
                    } else {
                        $remainder = $item['falseNum'] / $item['virtual_stock'];
                        if ($remainder > 0) {
                            $item['surplus_percentage'] = number_format($remainder, "2");
                        } else {
                            $item['surplus_percentage'] = 0;
                        }
                    }

                    if ($item['surplus_percentage'] > 1) {
                        $item['surplus_percentage'] = 1;
                    }
                    if (!is_array($item['surplus_percentage'])) {
                        $item['surplus_percentage'] = (string)$item['surplus_percentage'];
                    }
                    $item['surplus_percentage'] = (float)substr($item['surplus_percentage'], 0, strpos($item['surplus_percentage'], '.') + 3);
                    var_dump($item['surplus_percentage']);die;
                }
                if ($seckill['start_time'] > time()) {
                    $seckill['status'] = 0;//未开始
                } elseif ($seckill['end_time'] < time()) {
                    $seckill['status'] = 2;//已结束
                } elseif ($seckill['start_time'] < time() && $seckill['end_time'] > time()) {
                    $seckill['status'] = 1;//进行中
                }
                $seckill['start_time'] = date('Y-m-d H:i', $seckill['start_time']);
                $seckill['end_time'] = date('Y-m-d H:i', $seckill['end_time']);
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
            $newPprogressNum = $valArray['falseNum'];
        } else {
            //上次缓存的销量
            $valArray = json_decode($progressNum, true);

            //本次读取的销量
            $newKeyArray = json_decode($keyArray, true);

            //如果读取的和上次购买数量一致，返回缓存虚假销量
            if ($newKeyArray['buyNum'] == $valArray['buyNum']) {
                $newPprogressNum = $valArray['falseNum'];
            } elseif ($newKeyArray['buyNum'] > $valArray['buyNum']) {
                $valArray['falseNum'] = $newKeyArray['falseNum'];
                $valArray['buyNum'] = $newKeyArray['buyNum'];
                $cache->set($keyID, json_encode($valArray), $cacheTime);
                $newPprogressNum = $valArray['falseNum'];
            }
        }
        return $newPprogressNum;
    }

}