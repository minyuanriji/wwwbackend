<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 卡券操作
 * Author: zal
 * Date: 2020-04-18
 * Time: 11:50
 */

namespace app\forms\mall\statistics;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsCards;

class CardEditForm extends BaseModel
{
    public $name;
    public $pic_url;
    public $description;
    public $id;
    public $expire_type;
    public $time;
    public $expire_day;
    public $total_count;

    public function rules()
    {
        return [
            [['name', 'pic_url', 'description', 'expire_day', 'time', 'expire_type'], 'required'],
            [['pic_url', 'name', 'description'], 'string'],
            [['id', 'expire_type', 'expire_day', 'total_count'], 'integer'],
            [['total_count'], 'default', 'value' => -1]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->id) {
                $card = GoodsCards::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$card) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $card = new GoodsCards();
            }

            $card->name = $this->name;
            $card->mall_id = \Yii::$app->mall->id;
            $card->expire_type = $this->expire_type;
            $card->expire_day = $this->expire_day;
            $card->begin_time = $this->time[0];
            $card->end_at = $this->time[1];
            $card->pic_url = $this->pic_url;
            $card->description = $this->description;
            $card->total_count = $this->total_count;
            $res = $card->save();

            if (!$res) {
                throw new \Exception($this->responseErrorInfo($card));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
