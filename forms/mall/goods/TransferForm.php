<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\mall\goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsCatRelation;

class TransferForm extends BaseModel
{
    public $before;
    public $after;

    public function rules()
    {
        return [
            [['before', 'after'], 'required'],
            [['before', 'after'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'before' => '转移前分类',
            'after' => '转移后分类'
        ];
    }

    public function transfer()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->before <= 0) {
                throw new \Exception('必须选择转移前的分类');
            }
            if ($this->after <= 0) {
                throw new \Exception('必须选择转移后的分类');
            }
            $count = GoodsCatRelation::updateAll(
                ['cat_id' => $this->after],
                ['cat_id' => $this->before, 'is_delete' => 0]
            );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '转移成功，一共转移' . $count . '个商品',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
