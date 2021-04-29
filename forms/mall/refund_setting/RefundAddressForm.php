<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:42
 */

namespace app\forms\mall\refund_setting;


use app\core\ApiCode;

use app\core\BasePagination;
use app\models\BaseModel;
use app\models\RefundAddress;

/**
 * Class RefundAddressForm
 * @package app\forms\mall\refund_setting
 * @Notes
 */
class RefundAddressForm extends BaseModel
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'default', 'value' => ''],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '卡券ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = RefundAddress::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ]);
        /**
         * @var BasePagination $pagination
         */
        $query->keyword($this->keyword, ['or', ['like', 'name', $this->keyword], ['like', 'mobile', $this->keyword]]);
        $list = $query->page($pagination)->asArray()->all();


        foreach ($list as &$item) {
            $address = \Yii::$app->serializer->decode($item['address']);
            $add1 = isset($address[0]) ? $address[0] : '';
            $add2 = isset($address[1]) ? $address[1] : '';
            $add3 = isset($address[2]) ? $address[2] : '';

            $item['address'] = $add1 . ' ' . $add2 . ' ' . $add3 . ' ' . $item['address_detail'];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = RefundAddress::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'id' => $this->id
        ])->asArray()->one();

        $detail['address'] = \Yii::$app->serializer->decode($detail['address']);

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '数据异常,该条数据不存在',
        ];
    }

    public function delete()
    {

        try {
            $refundAddress = RefundAddress::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
            ]);

            if (!$refundAddress) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $refundAddress->is_delete = 1;
            $res = $refundAddress->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '删除失败',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
