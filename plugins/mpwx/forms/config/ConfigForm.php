<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信小程序插件-微信基础配置
 * Author: zal
 * Date: 2020-04-20
 * Time: 17:50
 */

namespace app\plugins\mpwx\forms\config;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mpwx\models\MpwxConfig;

class  ConfigForm extends BaseModel
{
    public $id;
    public $page;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '微信配置ID',
        ];
    }

    public function getDetail()
    {
        $detail = MpwxConfig::find()->where(['mall_id' => \Yii::$app->mall->id])->asArray()->one();

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '信息未配置',
        ];
    }
}
