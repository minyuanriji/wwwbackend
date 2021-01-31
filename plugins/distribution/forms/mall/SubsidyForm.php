<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\distribution\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyLevel;
use app\plugins\distribution\models\SubsidySetting;
use yii\base\Theme;

class SubsidyForm extends BaseModel
{

    public $keyword;
    public $page;
    public $id;
    public $min_num;
    public $max_num;
    public $price;
    public $is_enable;
    public $distribution_level;
    public $name;


    public function rules()
    {
        return [
            [['keyword', 'name'], 'string'],
            [['keyword', 'name'], 'trim'],
            [['price'], 'number'],
            [['page', 'id', 'distribution_level', 'min_num', 'max_num', 'is_enable'], 'integer'],
            [['page'], 'default', 'value' => 1],

        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = SubsidySetting::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ]);
        $list = $query->page($pagination, 20, $this->page)->orderBy(['id' => SORT_ASC])->asArray()->all();
        foreach ($list as &$item) {
            $distributionLevel = DistributionLevel::findOne(['level' => $item['distribution_level'], 'is_delete' => 0, 'mall_id' => $item['mall_id']]);
            if ($distributionLevel) {
                $item['distribution_level_name'] = $distributionLevel->name;
            } else {
                $item['distribution_level_name'] = '未知等级';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 16:34
     * @Note:保存等级
     */
    public function saveSubsidy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->id) {
            $setting = SubsidySetting::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$setting) {
                $setting = new SubsidySetting();
            }
        } else {
            $setting = new SubsidySetting();
        }
        $setting->mall_id = \Yii::$app->mall->id;
        $setting->attributes = $this->attributes;
        if (!$setting->save()) {
            return $this->responseErrorMsg($setting);
        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


}