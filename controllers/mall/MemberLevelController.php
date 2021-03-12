<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 11:46
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\mall\member\MemberLevelEditForm;
use app\forms\mall\member\MemberLevelForm;

class MemberLevelController extends UserManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new MemberLevelForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 12:29
     * @Note:编辑会员等级
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MemberLevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new MemberLevelForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new MemberLevelForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }


    /**
     * 获取会员等级列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new MemberLevelForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    /**
     * 获取所有会员等级
     * @return \yii\web\Response
     */
    public function actionAllMember()
    {
        $form = new MemberLevelForm();
        $res = $form->getAllMemberLevel();

        return $this->asJson($res);
    }

    public function actionSwitchStatus()
    {
        $form = new MemberLevelForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->switchStatus());
    }

    /**
     * 判断是否有会员卡插件权限
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function actionVipCardPermission()
    {
        $permission = \Yii::$app->branch->childPermission();
        if (!in_array('vip_card', $permission)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '无会员卡权限',
            ];
        }

        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $setting = $plugin->getSetting();
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '无会员卡权限',
            ];
        }

        $p= \Yii::$app->request->get('plugin','');
        if ($p && !in_array($p,$setting['rules'])) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => "插件{$p}无权限",
            ];
        }

        if ($setting['is_vip_card'] == 0) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '会员卡插件已关闭',
            ];
        }

        $card = $plugin->getCard();
        if (!$card) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '尚未添加会员卡',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '有会员卡权限',
        ];
    }
}