<?php

namespace app\plugins\seckill\forms\mall\special;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\seckill\models\Seckill;

class SpecialEditForm extends BaseModel
{
    public $id;
    public $name;
    public $start_time;
    public $end_time;
    public $pic_url;

    public function rules()
    {
        return [
            [['name', 'start_time', 'end_time'], 'required'],
            [['id'], 'integer'],
            [['name', 'start_time', 'end_time', 'pic_url'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $seckill = Seckill::findOne($this->id);
                if (!$seckill)
                    throw new \Exception('数据异常,该条数据不存在');

            } else {
                $seckill = new Seckill();
                $seckill->mall_id = \Yii::$app->mall->id;
            }
            $seckill->name = $this->name;
            $seckill->name = $this->name;
            $seckill->pic_url = $this->pic_url;
            $seckill->end_time = strtotime($this->end_time);

            if ($seckill->start_time > $seckill->end_time)
                throw new \Exception('开始时间不能大于结束时间！');

            if (!$seckill->save())
                throw new \Exception('保存失败');

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}