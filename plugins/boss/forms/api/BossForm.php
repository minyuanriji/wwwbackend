<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\boss\forms\api;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\User;
use app\plugins\boss\forms\common\Common;
use app\plugins\boss\models\BossOrderGoodsLog;
use app\plugins\boss\models\BossPriceLog;

class BossForm extends BaseModel
{
    public $page;
    public $level;
    public $limit;
    public $type;

    public function rules()
    {
        return [
            [['limit'], 'default', 'value' => 10],
            [['page', 'limit', 'level', 'type'], 'integer']
        ]; // TODO: Change the autogenerated stub
    }

    public function getInfo()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $user = User::findOne(\Yii::$app->user->identity->id);
        $common = Common::getCommon(\Yii::$app->mall);
        $returnData = $common->getBossInfo($user);

        if($returnData['is_boss']==0){

            return $this->returnApiResultData(ApiCode::CODE_FAIL, '你不是股东');

        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, ['info' => $returnData]);
    }

    public function getLogList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $user = User::findOne(\Yii::$app->user->identity->id);
        if (!$user) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '请先登录');
        }
        $query = BossOrderGoodsLog::find()
            ->alias('l')
            ->leftJoin(['c' => CommonOrderDetail::tableName()], 'c.id=l.common_order_detail_id')
            ->leftJoin(['u' => User::tableName()], 'u.id=c.user_id')
            ->where([
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        if ($this->type) {
            $query->andWhere(['l.type' => $this->type]);
        }
        if (!$this->type) {
            $query->andWhere(['l.type' => 0]);
        }
        if ($this->type == 0) {
            $query->andWhere(['l.type' => 0]);
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        /**
         * @var BasePagination $pagination
         */
        $query = $query->page($pagination, $this->limit, $this->page);
        $list = $query->select('l.*,c.order_no,u.avatar_url,u.nickname,c.price as goods_price,c.goods_id,c.goods_type')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);

            if ($item['goods_type'] == CommonOrderDetail::TYPE_MALL_GOODS) {
                $goods = Goods::findOne($item['goods_id']);
                if (!$goods) {
                    $item['goods_name'] = '未知名称';
                } else {
                    $item['goods_name'] = $goods->name;
                }
            }else{
                $item['goods_name'] = '非商城商品';
            }
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, $returnData);
    }


    public function getPriceList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $user = User::findOne(\Yii::$app->user->identity->id);
        if (!$user) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '请先登录');
        }
        $query = BossPriceLog::find()
            ->alias('l')
            ->where([
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        /**
         * @var BasePagination $pagination
         */
        $query = $query->page($pagination, $this->limit, $this->page);
        $list = $query->select('l.*')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $item['start_time'] = date('Y-m-d', $item['start_time']);
            $item['end_time'] = date('Y-m-d', $item['end_time']);
            $item['price_type']=$item['type']==1?'额外奖励':'永久分红';



        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, $returnData);
    }


}