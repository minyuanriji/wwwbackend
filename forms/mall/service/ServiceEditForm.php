<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 14:28
 */

namespace app\forms\mall\service;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsService;

class ServiceEditForm extends BaseModel
{


    public $name;
    public $remark;
    public $is_default;
    public $sort;
    public $id;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_default', 'id', 'sort'], 'integer'],
            [['remark'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $service = GoodsService::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
                if (!$service) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $service = new GoodsService();
                $service->mall_id = \Yii::$app->mall->id;
                $service->mch_id = \Yii::$app->admin->identity->mch_id;
            }

            $service->is_default = $this->is_default;
            $service->name = $this->name;
            $service->sort = $this->sort;
            $service->remark = $this->remark;
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