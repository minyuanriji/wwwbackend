<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 门店
 * Author: zal
 * Date: 2020-04-14
 * Time: 16:55
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use yii\helpers\ArrayHelper;

class StoreForm extends BaseModel
{
    public $id;
    public $page;
    public $is_default;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'is_default',], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword',], 'string'],
            [['keyword',], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '门店ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ]);

        $list = $query->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->orderBy('id desc')
            ->page($pagination)
            ->asArray()
            ->all();
        foreach ($list as $k => $v) {
            $list[$k]['latitude_longitude'] = $v['latitude'] . ',' . $v['longitude'];
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
        /** @var Store $detail */
        $detail = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ])->one();

        if ($detail) {
            $detail = ArrayHelper::toArray($detail);
            $detail['pic_url'] = json_decode($detail['pic_url']);
            $detail['latitude_longitude'] = $detail['latitude'] . ',' . $detail['longitude'];
            if ($detail['business_hours']) {
                $time = explode('-', $detail['business_hours']);
                $detail['start_time'] = $time[0];
                $detail['end_at'] = $time[1];
            }

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
            'msg' => '请求失败',
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $store = Store::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$store) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $store->is_delete = 1;
            $res = $store->save();

            if (!$res) {
                throw new \Exception('删除失败x01');
            }


            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 设置默认门店
     * @return array
     */
    public function switchDefault()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->is_default) {
                $store = Store::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'is_default' => 1
                ])->one();

                if ($store) {
                    $store->is_default = 0;
                    $res = $store->save();
                    if (!$res) {
                        throw new \Exception('更新失败x01');
                    }
                }
            }

            $store = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->one();
            if (!$store) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $store->is_default = $this->is_default;
            $store->save();


            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * 获取所有门店
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllStore()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ]);

        $list = $query->orderBy('id desc')->all();

        return $list;
    }
}
