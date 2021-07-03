<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-06
 * Time: 17:46
 */

namespace app\forms\api\coupon;


use app\core\ApiCode;
use app\forms\common\coupon\CouponListCommon;
use app\models\BaseModel;

class CouponListForm extends BaseModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $common = new CouponListCommon($this->attributes);
        $common->user = \Yii::$app->user->identity;
        $list = $common->getList();
        foreach ($list as &$item) {
            if (isset($item['couponCat'])) {
                unset($item['couponCat']);
            }
            if (isset($item['couponGood'])) {
                unset($item['couponGood']);
            }
            if ($item['appoint_type'] == 1) {
                $item['goods'] = [];
            }
            if ($item['appoint_type'] == 2) {
                $item['cat'] = [];
            }
            if ($item['appoint_type'] == 3) {
                $item['goods'] = [];
                $item['cat'] = [];
            }
            if ($item['expire_type'] == 2) {
                $item['begin_at'] = date('Y-m-d', strtotime($item['begin_at']));
                $item['end_at'] = date('Y-m-d', strtotime($item['end_at']));
            }
        }
        unset($item);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['list' => $list]);
    }

}