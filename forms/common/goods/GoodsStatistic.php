<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品统计
 * Author: zal
 * Date: 2020-04-13
 * Time: 17:16
 */

namespace app\forms\common\goods;

use app\models\BaseModel;
use app\models\Goods;
use app\models\Model;
use yii\db\Query;

class GoodsStatistic extends BaseModel
{
    public $mch_id;
    public $sign;

    /**
     * @var Query $query
     */
    public $query;

    public function rules()
    {
        return [
            [['mch_id', 'mch_id'], 'integer'],
            [['sign'], 'string'],
        ];
    }

    //持续改进
    public function getAll($params = [], $ignore = [])
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if (count($params) == 0) {
            $params = $this->getDefault();
        }

        $this->query = $query = Goods::find()->alias('g')->where([
            'g.mall_id' => \Yii::$app->mall->id,
            'g.is_delete' => 0,
            'g.sign' => $this->sign ?: '',
            'g.mch_id' => $this->mch_id ?: 0,
        ]);

        $result = [];
        foreach ($params as $item) {
            if (in_array($item, $ignore)) {
                continue;
            }
            $get = 'get' . hump($item);
            if (method_exists($this, $get)) {
                $result[$item] = $this->$get();
            }
        }

        return $result;
    }

    /**
     * @return array
     * 获取默认$params信息
     */
    private function getDefault()
    {
        return [
            'goods_count',
        ];
    }

    // 商品总数
    public function getGoodsCount()
    {
        $query = clone $this->query;
        if ($this->mch_id) {
            $query->joinWith(['mchGoods as mg' => function ($query1) {
                $query1->andWhere(['mg.status' => 2]);
            }]);
        }
        return $query->andWhere(['g.status' => 1])->count();
    }
}
