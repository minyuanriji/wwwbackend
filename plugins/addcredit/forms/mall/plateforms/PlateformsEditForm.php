<?php

namespace app\plugins\addcredit\forms\mall\plateforms;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsService;
use app\plugins\addcredit\models\AddcreditPlateforms;

class PlateformsEditForm extends BaseModel
{
    public $id;
    public $name;
    public $sdk_dir;
    public $ratio;
    public $cyd_id;
    public $secret_key;
    public $parent_id;
    public $transfer_rate;
    public $class_dir;
    public $enable_fast;
    public $enable_slow;

    public function rules()
    {
        return [
            [['name', 'sdk_dir', 'ratio', 'cyd_id', 'secret_key','parent_id', 'class_dir', 'transfer_rate'], 'required'],
            [['ratio', 'id', 'parent_id','transfer_rate', 'enable_fast', 'enable_fast'], 'integer'],
            [['name', 'sdk_dir', 'secret_key'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $plateforms = AddcreditPlateforms::findOne(['id' => $this->id]);
                if (!$plateforms) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $plateforms = new AddcreditPlateforms();
                $plateforms->mall_id = \Yii::$app->mall->id;
            }
            $plateforms->name = $this->name;
            $plateforms->sdk_dir = $this->sdk_dir;
            $plateforms->created_at = $plateforms->updated_at = time();
            $plateforms->ratio = $this->ratio;
            $plateforms->parent_id = $this->parent_id;
            $plateforms->transfer_rate = $this->transfer_rate;
            $plateforms->class_dir = $this->class_dir;
            $plateforms->enable_fast = (int)$this->enable_fast;
            $plateforms->enable_slow = (int)$this->enable_slow;
            $plateforms->json_param = json_encode(['id' => $this->cyd_id, 'secret_key' => $this->secret_key]);
            if (!$plateforms->save()) {
                throw new \Exception('保存失败');
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}