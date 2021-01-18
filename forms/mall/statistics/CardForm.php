<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 卡券
 * Author: zal
 * Date: 2020-04-18
 * Time: 11:50
 */

namespace app\forms\mall\statistics;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsCards;
use yii\helpers\ArrayHelper;

class CardForm extends BaseModel
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword',], 'default', 'value' => ''],
            [['keyword',], 'string'],
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

        $query = GoodsCards::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        $list = $query->keyword($this->keyword, ['like', 'name', $this->keyword])->page($pagination)->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getOptionList()
    {
        $list = GoodsCards::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->all();

        $newList = [];
        /** @var GoodsCards $item */
        foreach ($list as $item) {
            $newList[$item->id]['id'] = $item->id;
            $newList[$item->id]['name'] = $item->name;
            $newList[$item->id]['num'] = 1;
            $newList[$item->id]['count'] = $item->total_count == -1 ? '无限量' : $item->total_count;
        }

        $newList = array_values($newList);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList
            ]
        ];
    }

    public function getDetail()
    {
        $detail = GoodsCards::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->one();
        $detail = ArrayHelper::toArray($detail);
        $sign = '0000-00-00 00:00:00';
        if ($detail['begin_time'] == $sign || $detail['end_at'] == $sign) {
            $detail['time'] = [];
        } else {
            $detail['time'] = [$detail['begin_time'], $detail['end_at']];
        }

        if (!$detail) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请求失败',
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail,
            ]
        ];
    }

    public function destroy()
    {

        try {
            $card = GoodsCards::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$card) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $card->is_delete = 1;
            $res = $card->save();

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
