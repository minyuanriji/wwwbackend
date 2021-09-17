<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 14:09
 */

namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\PaymentOrder;
use app\models\User;

class BalanceLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $user_id;
    public $type;
    public $level;
    public $address;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id', 'level'], 'integer'],
            [['type'], 'string'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
            [['address'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $currentIncome = 0;
            $currentExpend = 0;
            if ($this->type) {
                $paymentQuery = PaymentOrder::find()->alias('po')->where(['po.is_pay' => 1, 'po.pay_type' => 3]);
            } else {
                $query = BalanceLog::find()->alias('b')->where([
                    'b.mall_id' => \Yii::$app->mall->id,
                ])->innerJoin(['u' => User::tableName()], 'u.id=b.user_id')
                    ->andWhere(['and',['<>', 'u.mobile', ''],['IS NOT', 'u.mobile', NULL],['u.is_delete' => 0]]);
                if ($this->keyword) {
                    $query->where(['like', 'u.nickname', $this->keyword]);
                }
                if ($this->user_id) {
                    $query->andWhere(['b.user_id' => $this->user_id]);
                }
                if ($this->start_date && $this->end_date) {
                    $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                        ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
                }
                $incomeQuery = clone $query;
                $income = $incomeQuery->andWhere(['b.type' => 1])->sum('b.money');
                $expendQuery = clone $query;
                $expend = $expendQuery->andWhere(['b.type' => 2])->sum('b.money');
                $list = $query->select(['b.*','u.id as uid','u.nickname'])->page($pagination, $this->limit)->orderBy('b.id desc')->asArray()->all();

                foreach ($list as &$v) {
                    if(!empty($v['custom_desc'])){
                        $v['info_desc'] = SerializeHelper::decode($v['custom_desc']);
                    }else{
                        $v['info_desc'] = [];
                    }
                    if ($v['type'] == 1) {
                        $currentIncome += $v['money'];
                    } else {
                        $currentExpend += $v['money'];
                    }
                }
                unset($v);
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '' ,[
                'list' => $list,
                'Statistics' => [
                    'income' => $income ?: 0,
                    'expend' => $expend ?: 0,
                    'currentIncome' => sprintf("%.2f",$currentIncome),
                    'currentExpend' => sprintf("%.2f",$currentExpend),
                ],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}