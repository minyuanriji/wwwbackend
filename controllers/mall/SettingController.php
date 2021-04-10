<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 16:14
 */

namespace app\controllers\mall;

use app\forms\mall\member\RegisterAgreeForm;
use app\forms\mall\setting\MailSettingForm;
use app\forms\mall\option\RechargeSettingForm;
use app\forms\mall\setting\MallForm;
use app\forms\mall\setting\MallSettingForm;
use app\forms\mall\setting\OperateLogForm;
use app\forms\mall\setting\SettingForm;
use app\forms\mall\setting\TagForm;
use app\forms\mall\setting\UserVisitLogForm;
use app\forms\mall\sms\SmsEditForm;
use app\forms\mall\sms\SmsForm;
use app\helpers\SerializeHelper;
use app\models\MailSetting;
use app\services\MallSetting\MallSettingService;

class SettingController extends MallController
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:18
     * @Note:设置页面
     * @return string|\yii\web\Response
     */
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new MallForm();
                $res = $form->getDetail();
                return $this->asJson($res);
            } else {
                $form = new SettingForm();
                
                $data = \Yii::$app->serializer->decode(\Yii::$app->request->post('ruleForm'));
                $form->name = $data['name'];
                $form->attributes = $data['setting'];
                
                $recharge = new RechargeSettingForm();
                $recharge->attributes = $data['recharge'];
                $recharge->set();
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:18
     * @Note:规则页面配置
     * @return string
     */
    public function actionRule()
    {
        return $this->render('rule');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:18
     * @Note:消息提醒
     * @return string
     */
    public function actionNotice()
    {
        return $this->render('notice');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:18
     * @Note:邮件提醒
     * @return string
     */
    public function actionMail()
    {
        if (\Yii::$app->request->isAjax) {
            $model = MailSetting::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => \Yii::$app->admin->identity->mch_id
            ]);
            if (!$model) {
                $model = new MailSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->admin->identity->mch_id;
            }
            if ($model->receive_mail) {
                $model->receive_mail = explode(',', $model->receive_mail);
            }
            if (!$model->receive_mail) {
                $model->receive_mail = [];
            }
            if (\Yii::$app->request->isPost) {
                $form = new MailSettingForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                return $this->asJson([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('mail');
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-23
     * @Time: 16:18
     * @Note:短信提醒
     * @return string
     */
    public function actionSms()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SmsEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            } else {
                $form = new SmsForm();
                return $form->getDetail();
            }
        }

        return $this->render('sms');
    }

    /**
     * 短信测试
     * @param $type
     * @return \yii\web\Response
     */
    public function actionTestSms($type)
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SmsEditForm();
            $form->attributes = \Yii::$app->request->post('form');
            return $this->asJson($form->testSms($type));
        } else {
            \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl('mall/setting/sms'));
        }
    }

    /**
     * 微信模板消息设置
     * @return string|void|\yii\web\Response
     */
    public function actionTemplate()
    {
        return;
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new WxPlatformMsgForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new WxPlatformEditForm();
                $data = \Yii::$app->request->post('form');
                $form->attributes = $data;
                $form->template_list = $data['template_list'];
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-31
     * @Time: 16:18
     * @Note: 标签设置
     * @return string
     */
    public function actionTag()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TagForm();
                $form->data = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new TagForm();
                $form->id = \Yii::$app->request->get("id");
                return $form->getDetail();
            }
        }

        return $this->render('tag');
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-08-03
     * @Time: 16:18
     * @Note: 标签列表
     * @return string
     */
    public function actionTagList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TagForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->getList();
            } else {
                $form = new TagForm();
                return $form->getList();
            }
        }

        return $this->render('tag_list');
    }

    /**
     * 操作日志
     * @return string|\yii\web\Response
     */
    public function actionOperateLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new OperateLogForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getList();
                return $this->asJson($res);
            }
        } else {
            return $this->render('operate-log');
        }
    }

    /**
     * 获取|更新 商城设置
     * xuyaoxiang
     * @return \yii\web\Response
     */
    public function actionMall()
    {
        if (\Yii::$app->request->isAjax) {
            $MallSettingForm = new MallSettingForm();
            //更新
            if (\Yii::$app->request->isPost) {
                $MallSettingForm->attributes = \Yii::$app->request->post();
                return $this->asJson($MallSettingForm->store());
            } else {
                //获取
                $MallSettingForm->attributes = \Yii::$app->request->get();
                $res                         = $MallSettingForm->getValueByKeyApiData();
                return $this->asJson($res);
            }
        }
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-11-02
     * @Time: 14:18
     * @Note:注册协议
     * @return string|\yii\web\Response
     */
    public function actionRegisterAgree()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new RegisterAgreeForm();
                $res = $form->getDetail();
                return $this->asJson($res);
            } else {
                $form = new RegisterAgreeForm();
                $data = SerializeHelper::decode(\Yii::$app->request->post('ruleForm'));;
                $form->data = $data;
                return $form->save();
            }
        } else {
            return $this->render('register-agree');
        }
    }

    /**
     * 用户日志
     * @return string|\yii\web\Response
     */
    public function actionUserLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new UserVisitLogForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getList();
                return $this->asJson($res);
            }
        } else {
            return $this->render('user-log');
        }
    }
}