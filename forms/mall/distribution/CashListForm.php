<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现列表
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;


use app\forms\common\distribution\DistributionCashListCommon;
use app\models\BaseModel;

class CashListForm extends BaseModel
{
    public $mall;

    public $page;
    public $limit;

    public $status;

    public $start_date;
    public $end_date;
    public $keyword;
    public $platform;

    public $fields;
    public $flag;
    public $user_id;

    public function rules()
    {
        return [
            [['status'], 'required'],
            [['page', 'limit', 'status', 'user_id'], 'integer'],
            [['fields'], 'safe'],
            [['flag'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $form = new DistributionCashListCommon($this->attributes);
        return [
            'code' => 0,
            'data' => $form->search()
        ];
    }
}
