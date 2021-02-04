<?php
namespace app\mch\controllers;

use app\core\ApiCode;
use app\forms\mall\member\MemberLevelForm;

class MemberLevelController extends MchController {

    /**
     * 获取所有会员等级
     * @return \yii\web\Response
     */
    public function actionAllMember() {
        $form = new MemberLevelForm();
        $res = $form->getAllMemberLevel();

        return $this->asJson($res);
    }

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