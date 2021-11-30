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
    public $parent_id;
    public $transfer_rate;
    public $class_dir;
    public $enable_fast;
    public $enable_slow;
    public $allow_plats;
    public $pattern_deny;
    public $region_deny;
    public $params;

    public function rules()
    {
        return [
            [['name', 'sdk_dir', 'ratio', 'class_dir', 'transfer_rate'], 'required'],
            [['ratio', 'id', 'parent_id','transfer_rate', 'enable_fast', 'enable_slow','parent_id'], 'integer'],
            [['name', 'sdk_dir', 'allow_plats', 'pattern_deny'], 'string'],
            [['region_deny', 'params'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $plateforms = AddcreditPlateforms::findOne($this->id);
                if (!$plateforms)
                    throw new \Exception('数据异常,该条数据不存在');

            } else {
                $plateforms = new AddcreditPlateforms();
                $plateforms->mall_id = \Yii::$app->mall->id;
            }
            $plateforms->name           = $this->name;
            $plateforms->sdk_dir        = $this->sdk_dir;
            $plateforms->ratio          = $this->ratio;
            $plateforms->parent_id      = $this->parent_id ?? 0;
            $plateforms->transfer_rate  = $this->transfer_rate;
            $plateforms->class_dir      = $this->class_dir;
            $plateforms->enable_fast    = (int)$this->enable_fast;
            $plateforms->enable_slow    = (int)$this->enable_slow;
            $plateforms->allow_plats    = $this->allow_plats;
            $plateforms->pattern_deny   = $this->pattern_deny;
            $plateforms->region_deny    = !empty($this->region_deny) ? json_encode($this->region_deny) : "";
            $plateforms->json_param     = !empty($this->params) ? json_encode($this->params) : "";
            if (!$plateforms->save())
                throw new \Exception($plateforms->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}