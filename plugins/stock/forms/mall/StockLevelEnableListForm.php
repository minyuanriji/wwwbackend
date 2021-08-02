<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-10
 * Time: 22:29
 */

namespace app\plugins\stock\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\models\StockLevel;


class StockLevelEnableListForm extends BaseModel
{
    public function getList()
    {
        $list = StockLevelCommon::getEnableLevelList();
        $equal_level_list = StockLevel::find()->where(['is_equal' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
        foreach ($equal_level_list as &$price) {
            $price['equal_price'] = 0;
        }
        $fill_level_list = StockLevel::find()->where(['is_fill' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
        foreach ($fill_level_list as &$price) {
            $price['fill_price'] = 0;
        }

        $over_level_list = StockLevel::find()->where(['is_over' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
        foreach ($over_level_list as &$price) {
            $price['over_price'] = 0;
        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['list' => $list,'equal_level_list'=>$equal_level_list,'fill_level_list'=>$fill_level_list,'over_level_list'=>$over_level_list]];
    }
}