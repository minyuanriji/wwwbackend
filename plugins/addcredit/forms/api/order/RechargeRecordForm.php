<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

class RechargeRecordForm extends BaseModel
{
    public $plateforms_id;
    public $page;
    public $recharge_time;
    public $is_list;

    public function rules()
    {
        return [
            [['plateforms_id'], 'required'],
            [['page', 'is_list'], 'integer'],
            [['recharge_time'], 'string'],
        ];
    }

    public function RechargeList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrder::find();

            if ($this->is_list) {
                $select = ["id", "mobile", "order_price", "pay_status", "order_status", "DATE_FORMAT(FROM_UNIXTIME(created_at),'%H:%i:%s') as created_at"];
                $sameDay = date('Y-m-d', time());
                $query->andWhere('FROM_UNIXTIME(created_at,"%Y-%m-%d")="' . $sameDay . '"');
                $limit = 999;
            } else {
                $select = ["id", "mobile", "order_price", "pay_status", "order_status", "DATE_FORMAT(FROM_UNIXTIME(created_at),'%Y-%m-%d %H:%i:%s') as created_at"];
                if ($this->recharge_time) {
                    $query->andWhere('FROM_UNIXTIME(created_at,"%Y-%m-%d")="' . $this->recharge_time . '"');
                }
                $limit = 10;
            }

            $query->andWhere(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id])->select($select);

            $result = $query->orderBy('created_at DESC')->page($pagination, $limit)->asArray()->all();
            if($result){
                foreach($result as &$item){
                    $item['order_status'] = $item['pay_status'] == "paid" ? "success" : $item['order_status'];
                }
            }

            $user = User::findOne(\Yii::$app->user->id);

                //获取上次充值手机号码
            $mobile = AddcreditOrder::find()->andWhere(['user_id' => $user->id, 'mall_id' => \Yii::$app->mall->id])
                ->select('mobile')->asArray()->orderBy('created_at DESC')->one();



            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result,
                'money_list' => $this->rechargeMoneyList(),
                'mobile' => $mobile ? $mobile['mobile'] : (string)$user->mobile,
                'msg' => '',
                'pagination' => $pagination,

            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function rechargeMoneyList ()
    {
        $plateforms = AddcreditPlateforms::find()->where(["is_enabled" => 1])->orderBy("id DESC")->one();
        if (!$plateforms) {
            throw new \Exception('平台信息不存在！',ApiCode::CODE_FAIL);
        }

        $products = @json_decode($plateforms->product_json_data, true);
        $groupDatas = ['FastCharging' => [], 'SlowCharge' => []];
        $groupDatas['enable_fast'] = $plateforms->enable_fast;
        $groupDatas['enable_slow'] = $plateforms->enable_slow;
        if($products){
            foreach($products as $item){
                if($item['type'] == "fast"){
                    $groupDatas['FastCharging'][] = array_merge($item, [
                        'redbag_num'   => $item['price'] + $item['price'] * $plateforms->ratio / 100,
                        'plateform_id' => $plateforms->id
                    ]);
                }else{
                    $groupDatas['SlowCharge'][] = array_merge($item, [
                        'redbag_num'   => $item['price'] + $item['price'] * $plateforms->ratio / 100,
                        'plateform_id' => $plateforms->id
                    ]);
                }
            }
        }
        return $groupDatas;
    }
}