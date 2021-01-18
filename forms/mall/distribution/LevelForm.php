<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商等级查询
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistributionLevel;
use app\models\DistributionSetting;

class LevelForm extends BaseModel
{
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = DistributionLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ])->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)->orderBy(['level' => SORT_ASC])->all();
        $level = DistributionSetting::get(\Yii::$app->mall->id, DistributionSetting::LEVEL, 0);
        array_walk($list, function (&$item) use ($level) {
            $item->condition = round($item->condition, 2);
            switch ($level) {
                case 0:
                    $item->first = -1;
                    $item->second = -1;
                    $item->third = -1;
                    break;
                case 1:
                    $item->second = -1;
                    $item->third = -1;
                    break;
                case 2:
                    $item->third = -1;
                    break;
                default:
            }
        });
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
