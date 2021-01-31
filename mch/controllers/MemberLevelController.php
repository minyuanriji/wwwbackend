<?php
namespace app\mch\controllers;

use app\core\ApiCode;

class MemberLevelController extends MchController {

    /**
     * 判断是否有会员卡插件权限
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function actionVipCardPermission(){
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '会员卡插件已关闭',
        ];
    }
}