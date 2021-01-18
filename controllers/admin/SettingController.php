<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 管理员后台设置
 * Author: zal
 * Date: 2020-04-09
 * Time: 17:12
 */

namespace app\controllers\admin;


use app\component\jobs\TestQueueServiceJob;
use app\controllers\behavior\SuperAdminFilter;
use app\core\ApiCode;
use app\forms\admin\mall\MallOverrunForm;
use app\forms\common\AttachmentForm;
use app\forms\common\AttachmentUploadForm;
use app\forms\common\FileForm;
use app\logic\OptionLogic;
use app\models\Option;
use yii\web\UploadedFile;

class SettingController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
                'safeRoutes' => [
                    'admin/setting/small-routine',
                    'admin/setting/upload-file',
                    'admin/setting/attachment',
                    'admin/setting/attachment-create-storage',
                    'admin/setting/attachment-enable-storage',
                ]
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $setting = \Yii::$app->request->post('setting');
                if (OptionLogic::set(Option::NAME_IND_SETTING, $setting)) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功。',
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '保存失败。',
                    ];
                }
            } else {
                $setting = OptionLogic::get(Option::NAME_IND_SETTING);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'setting' => $setting,
                    ],
                ];
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 获取附件信息
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:33
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionAttachment()
    {
        if (\Yii::$app->request->isAjax) {
            $user = \Yii::$app->admin->identity;
            $common = AttachmentForm::getCommon($user);
            $list = $common->getAttachmentList();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'storageTypes' => $common->getStorageType()
                ]
            ]);
        } else {
            return $this->render('attachment');
        }
    }

    /**
     * 上传附件
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:33
     * @return array
     * @throws \Exception
     */
    public function actionAttachmentCreateStorage()
    {
        try {
            $admin = \Yii::$app->admin->identity;
            $common = AttachmentForm::getCommon($admin);
            $data = \Yii::$app->request->post();
            $common->attachmentCreateStorage($data);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 删除附件
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:33
     * @param $id
     * @return \yii\web\Response
     */
    public function actionAttachmentEnableStorage($id)
    {
        $common = AttachmentForm::getCommon(\Yii::$app->admin->identity);
        $common->attachmentEnableStorage($id);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionSmallRoutine()
    {
        return $this->render('small-routine');
    }

    /**
     * 上传业务域名文件
     * @Author: zal
     * @Date: 2020-04-10
     * @Time: 15:33
     * @param $id
     * @return \yii\web\Response
     */
    public function actionUploadFile($name = 'file')
    {
        $form = new FileForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }

    /**
     * 上传logo文件
     * @Author: zal
     * @Date: 2020-04-10
     * @Time: 15:33
     * @param $id
     * @return \yii\web\Response
     */
    public function actionUploadLogo($name = 'file')
    {
        $form = new FileForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->saveLogo());
    }

    /**
     * 队列服务
     * @Author: zal
     * @Date: 2020-05-21
     * @Time: 10:33
     * @param $id
     * @return \yii\web\Response
     */
    public function actionQueueService($action = null, $id = null)
    {
        if (\Yii::$app->request->isAjax) {
            if ($action == 'create') {
                try {
                    $id = \Yii::$app->queue->delay(0)->push(new TestQueueServiceJob());
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test') {
                $done = \Yii::$app->queue->isDone($id);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'done' => $done ? true : false,
                    ],
                ];
            }
        } else {
            return $this->render('queue-service');
        }
    }

    /**
     * 超限设置
     * @return string|\yii\web\Response
     */
    public function actionOverrun()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
                $form = new MallOverrunForm();
                $form->form = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new MallOverrunForm();
                return $this->asJson($form->setting());
            }
        } else {
            return $this->render('overrun');
        }
    }
}
