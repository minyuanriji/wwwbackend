<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商户管理
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\forms\common\mch;

use app\forms\mall\mch\AccountLogExport;
use app\models\BaseModel;
use app\models\Order;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCash;
use app\plugins\mch\models\MchOrder;

class CommonFinancialManageForm extends BaseModel
{
    public $is_transfer;
    public $pagination;
    public $mch_id;
    public $keyword;
    public $start_date;
    public $end_date;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['flag',], 'trim'],
            [['mch_id'], 'required'],
            [['is_transfer', 'mch_id'], 'integer'],
            [['is_transfer'], 'default', 'value' => 0],
            [['keyword', 'start_date', 'end_date', 'fields'], 'string'],
        ];
    }

    public function getAccountLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $query = MchAccountLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
        ]);

        if ($this->keyword) {
            $query->where(['like', 'desc', $this->keyword]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'created_at', $this->end_date])
                ->andWhere(['>', 'created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new AccountLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination)->asArray()->all();

        $this->pagination = $pagination;
        return $list;
    }

    public function getCashLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = MchCash::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
        ]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'created_at', $this->end_date])
                ->andWhere(['>', 'created_at', $this->start_date]);
        }

        $list = $query->page($pagination)->asArray()->all();

        $this->pagination = $pagination;
        return $list;
    }

    public function getOrderCloseLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $orderIds = MchOrder::find()->where([
            'is_transfer' => $this->is_transfer
        ])->select('order_id');


        $query = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
            'id' => $orderIds
        ]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'created_at', $this->end_date])
                ->andWhere(['>', 'created_at', $this->start_date]);
        }

        $list = $query->select('id')->with('mchOrder', 'detail.goods')->page($pagination)->asArray()->all();

        $this->pagination = $pagination;
        return $list;
    }
}
