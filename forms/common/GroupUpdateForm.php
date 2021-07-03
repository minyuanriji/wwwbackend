<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 附件分组表单
 * Author: zal
 * Date: 2020-04-11
 * Time: 16:12
 */

namespace app\forms\common;

use app\core\ApiCode;
use app\models\AttachmentGroup;
use app\models\BaseModel;

class GroupUpdateForm extends BaseModel
{
    public $id;
    public $name;
    public $mall_id;
    public $mch_id;
    public $type;

    public function rules()
    {
        return [
            [['name',], 'trim'],
            [['name', 'mall_id', 'mch_id'], 'required'],
            [['id', 'type'], 'integer',],
            [['name',], 'string', 'max' => 64,],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        $model = AttachmentGroup::findOne([
            'id' => $this->id,
            'mall_id' => $this->mall_id,
            'is_delete' => 0,
            'mch_id' => $this->mch_id,
        ]);
        if (!$model) {
            $model = new AttachmentGroup();
            $model->mall_id = $this->mall_id;
            $model->mch_id = $this->mch_id;
            $model->type = $this->type;
        }
        $model->name = $this->name;
        if (!$model->save()) {
            return $this->responseErrorInfo($model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
            'data' => $model,
        ];
    }
}
