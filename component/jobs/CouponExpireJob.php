<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 优惠券到期处理
 * Author: zal
 * Date: 2020-06-04
 * Time: 19:16
 */

namespace app\component\jobs;

use app\models\BaseModel;
use app\models\Coupon;
use app\models\UserCoupon;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CouponExpireJob extends BaseObject implements JobInterface
{
    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("--CouponExpireJob start id:".$this->id."--");
        $t = \Yii::$app->db->beginTransaction();
        try {
            $coupon = Coupon::findOne([
                'id' => $this->id,
                //'is_delete' => 0,
                'is_failure' => 0,
            ]);
            \Yii::warning("CouponExpireJob coupon=".var_export($coupon,true));
            if (!$coupon) {
                return;
            }
            if ($coupon->is_failure == 1) {
                return ;
            }
            $coupon->is_failure = Coupon::YES;
            if(!$coupon->save()){
                throw new \Exception((new BaseModel())->responseErrorMsg($coupon));
            }
            $userCouponIds = UserCoupon::find()->select("id")->where(['coupon_id' => $this->id,"is_delete" => 0,'is_use' => UserCoupon::NO])->column();
            \Yii::warning("CouponExpireJob coupon=".var_export($userCouponIds,true));
            if(!empty($userCouponIds)){
                $res = UserCoupon::updateAll(["is_failure" => UserCoupon::YES,"updated_at" => time()],["in","id",$userCouponIds]);
                if ($res === false) {
                    throw new \Exception((new BaseModel())->responseErrorMsg());
                }
            }
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error("CouponExpireJob error File:".$exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage());
        }
    }
}
