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

    public function rules()
    {
        return [
            [['name', 'sdk_dir', 'ratio', 'cyd_id', 'secret_key','parent_id'], 'required'],
            [['ratio', 'id', 'cyd_id', 'parent_id'], 'integer'],
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
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
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
            $plateforms->json_param = json_encode(['id' => $this->cyd_id, 'secret_key' => $this->secret_key]);
            if ($plateforms->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}