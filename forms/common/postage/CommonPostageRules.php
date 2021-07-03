<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 11:01
 */

namespace app\forms\common\postage;
use app\core\ApiCode;
use app\models\PostageRules;

class CommonPostageRules
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 11:03
     * @Note:设置默认的运费规则(一个商城仅有一个默认运费规则)
     * @param null $id
     * @return array
     */
    public static function setStatus($id = null)
    {
        $model = PostageRules::findOne([
            'id' => $id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '运费规则不存在'
            ];
        } else {
            PostageRules::updateAll(['status' => 0], [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
            ]);
            $model->status = 1;
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功'
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 11:03
     * @Note:删除运费规则
     * @param null $id
     * @return array
     */
    public static function deleteItem($id = null)
    {
        $model = PostageRules::findOne([
            'id' => $id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '没有可删除的选项'
            ];
        } else {
            $model->is_delete = 1;
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 11:03
     * @Note:删除所有运费规则
     * @return array
     */
    public static function deleteItemAll()
    {
        $count = PostageRules::updateAll(['is_delete' => 1], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ]);
        if ($count > 0) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "删除成功，共删除{$count}个"
            ];
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '没有可删除的选项'
            ];
        }
    }
}