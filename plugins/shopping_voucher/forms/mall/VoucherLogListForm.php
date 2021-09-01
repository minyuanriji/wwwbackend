<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\Store;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\Shopping_voucher\models\ShoppingVoucherLog;

class VoucherLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = ShoppingVoucherLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
            'b.is_delete' => 0,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->andWhere(['or', ['like', 'mobile', $this->keyword], ['like', 'nickname', $this->keyword]]);
            }
        }])->orderBy('id desc');
        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
        }
        $list = $query->page($pagination, $this->limit)->asArray()->all();
        if ($list) {
            foreach ($list as &$v) {
                $v['info_desc'] = $v['custom_desc'] ? SerializeHelper::decode($v['custom_desc']) : [];
            }
        }
        unset($v);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list ?: [],
            'pagination' => $pagination,
        ]);
    }
}