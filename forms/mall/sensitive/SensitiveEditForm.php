<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 14:28
 */

namespace app\forms\mall\sensitive;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Sensitive;

class SensitiveEditForm extends BaseModel
{
    public $sensitive;
    public $id;

    public function rules()
    {
        return [
            [['sensitive'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $service = Sensitive::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
                if (!$service) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $service = new Sensitive();
                $service->mall_id = \Yii::$app->mall->id;
                $service->mch_id = \Yii::$app->admin->identity->mch_id;
            }

            $service->sensitive = $this->sensitive;
            $res = $service->save();

            if ($res) {
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