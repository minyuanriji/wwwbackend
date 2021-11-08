<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

class OilPlateformEditForm extends BaseModel {

    public $id;
    public $name;
    public $sdk_src;
    public $region_deny;
    public $params;

    public function rules()
    {
        return [
            [['name', 'sdk_src'], 'required'],
            [['id'], 'integer'],
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
                $plateform = OilPlateforms::findOne($this->id);
                if (!$plateform) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
                if($plateform->is_enabled){
                    OilPlateforms::updateAll(["is_enabled" => 0], "id <> '{$this->id}'");
                }
            } else {
                $plateform = new OilPlateforms([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time(),
                    "is_enabled" => 0
                ]);
            }

            $plateform->name        = $this->name;
            $plateform->sdk_src     = $this->sdk_src;
            $plateform->updated_at  = time();
            $plateform->region_deny = !empty($this->region_deny) ? json_encode($this->region_deny) : "";
            $plateform->json_param  = !empty($this->params) ? json_encode($this->params) : "";
            if (!$plateform->save()) {
                throw new \Exception($this->responseErrorMsg($plateform));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}