<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-25
 * Time: 10:17
 */


namespace app\forms\mall\goods;


use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class RecommendSettingForm extends BaseModel
{
    public $data;
    public function getSetting()
    {
        $form = new \app\forms\common\goods\RecommendSettingForm();
        $setting = $form->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }

    public function save()
    {
        try {
            $data = \Yii::$app->serializer->decode($this->data);
            if ($data['goods']['goods_num'] > 10) {
                throw new \Exception('推荐商品显示数量最多10个');
            }
            $data['goods']['goods_num'] = (int)$data['goods']['goods_num'];

            $setting = OptionLogic::set(
                Option::NAME_RECOMMEND_SETTING,
                $data,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}