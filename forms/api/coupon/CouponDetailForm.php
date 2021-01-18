<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-05
 * Time: 9:38
 */

namespace app\forms\api\coupon;


use app\core\ApiCode;
use app\forms\common\coupon\CouponCommon;
use app\models\BaseModel;

class CouponDetailForm extends BaseModel
{

    public $coupon_id;
    public function rules()
    {
        return [
            ['coupon_id', 'required'],
            ['coupon_id', 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try  {
            $common = new CouponCommon($this->attributes, true);
            $common->user = \Yii::$app->user->identity;
            $res = $common->getDetail();
            if ($res['is_delete'] == 1) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'优惠券不存在！');
            }
            if (isset($res['couponCat'])) {
                unset($res['couponCat']);
            }
            if (isset($res['couponGood'])) {
                unset($res['couponGood']);
            }
            if ($res['appoint_type'] == 1) {
                $res['goods'] = [];
            }
            if ($res['appoint_type'] == 2) {
                $res['cat'] = [];
            }
            if ($res['appoint_type'] == 3) {
                $res['goods'] = [];
                $res['cat'] = [];
            }
            $res['page_url'] = '/pages/goods/list?coupon_id=' . $res['id'];
            if ($res['appoint_type'] == 4) {
                $res['page_url'] = '/plugins/scan_code/index/index';
            }
            $res['begin_at'] = date('Y-m-d', strtotime($res['begin_at']));
            $res['end_at'] = date('Y-m-d', strtotime($res['end_at']));
            $res['receive_count'] = !\Yii::$app->user->isGuest ? $common->checkReceive($res['id']) : '0';
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['coupon' => $res]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}