<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 页面标题展示
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\forms\common\PickLinkForm;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class PageTitleForm extends BaseModel
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-27
     * @Time: 14:21
     * @Note:页面标题列表
     * @return array
     */
    public function getList()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => AppConfigLogic::getPageTitleConfig()
            ]
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-27
     * @Time: 14:21
     * @Note:恢复默认
     * @return array
     */
    public function restoreDefault()
    {
        $res = OptionLogic::set(
            Option::NAME_PAGE_TITLE,
            PickLinkForm::getCommon()->getTitle(),
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );

        if (!$res) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '恢复失败',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '恢复成功',
        ];
    }
}
