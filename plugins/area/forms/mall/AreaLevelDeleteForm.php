<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 15:26
 */

namespace app\plugins\area\forms\mall;


use app\core\ApiCode;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaLevel;
use app\models\BaseModel;

class AreaLevelDeleteForm extends BaseModel
{


    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ]; // TODO: Change the autogenerated stub
    }

    public function save()
    {

        if (!$this->validate()) {

            return $this->responseErrorInfo();
        }
        $level = AreaLevel::findOne(['id' => $this->id, 'is_delete' => 0]);

        if (!$level) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '所选择的分销商等级不存在或已删除，请刷新后重试'];
        }
        $areaExists = Area::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'level' => $level->level
        ])->exists();
        if ($areaExists) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '该分销商等级下还有分销商存在，暂时不能删除'];
        }
        $level->is_delete = 1;
        if (!$level->save()) {
            return $this->responseErrorMsg($level);

        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！'];

    }


}