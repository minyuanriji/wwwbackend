<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchCommonCat;

class CommonCatEditForm extends BaseModel
{
    public $id;
    public $name;
    public $sort;
    public $status;

    public function rules()
    {
        return [
            [['name', 'sort', 'status'], 'required'],
            [['id', 'sort', 'status'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }
        
        try {
            
            if ($this->id) {
                $model = MchCommonCat::findOne($this->id);
                if (!$model) {
                    throw new \Exception('类目不存在');
                }
            } else {
                //var_dump(\Yii::$app->mall->id);exit;
                $model = new MchCommonCat();
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->name = $this->name;
            $model->sort = $this->sort;
            $model->status = $this->status;
            
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
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
