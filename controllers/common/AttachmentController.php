<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-06
 * Time: 17:30
 */

namespace app\controllers\common;

use app\controllers\BaseController;
use app\core\ApiCode;
use app\forms\common\attachment\CommonAttachment;
use app\forms\common\GroupUpdateForm;
use app\models\Admin;
use app\models\AttachmentGroup;
use app\models\AttachmentInfo;
use app\models\BaseModel;
use app\models\Mall;

/**
 * Class AttachmentController
 * @package app\controllers\common
 *  附件类:负责附件上传和管理附件文件
 */
class AttachmentController extends BaseController
{
    private $mchId;
    private $jxMall;

    public function init(){
        parent::init();
    }

    public function actionUpload($name = 'image')
    {
        $mall = $this->getMall();
        $admin_id = \Yii::$app->admin->id;
        //来源1后台2前台
        $from = 1;
        $group_id = \Yii::$app->request->get('attachment_group_id');
        $result = CommonAttachment::addAttachmentInfo($from,$mall->id,$admin_id,$group_id);
        return $result;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-06
     * @Time: 18:51
     * @Note:附件列表
     * @return array
     */
    public function actionList($page = 0, $attachment_group_id = null, $type = 'image', $is_recycle = null, $keyword = null)
    {

        $mall = $this->getMall();
        if (!$mall) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'data' => 'Mall为空，请刷新页面后重试。'
            ];
        }
        $query = AttachmentInfo::find()->where([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'type' => $type,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);

        !is_null($is_recycle) && $query->andWhere(['is_recycle' => $is_recycle]);
        !is_null($keyword) && $query->keyword($keyword, ['like', 'name', $keyword]);
        $attachment_group_id && $query->andWhere(['group_id' => $attachment_group_id]);

        $list = $query
            ->orderBy('id DESC')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['thumb_url'] = $item['thumb_url'] ? $item['thumb_url'] : $item['url'];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ],
        ];
    }

    public function actionGroupList($type = null, $is_recycle = null)
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => 0,
                'data' => [
                    'no_mall' => true,
                    'list' => [],
                ],
            ]);
        }
        $query = AttachmentGroup::find()->where([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);

        is_null($type) || $query->andWhere(['type' => $type === 'video' ? 1 : 0]);
        is_null($is_recycle) || $query->andWhere(['is_recycle' => $is_recycle]);

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $query->all(),
            ],
        ]);
    }

    /**
     * 附件分组修改
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:55
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionGroupUpdate()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'data' => 'Mall为空数组，请刷新页面后重试。'
            ]);
        }

        $form = new GroupUpdateForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mall_id = $mall->id;
        $form->type = \Yii::$app->request->post('type') == 'video' ? 1 : 0;
        $form->mch_id = $this->getMchId() ? $this->getMchId() : 0;
        return $this->asJson($form->save());
    }

    /**
     * 附件分组删除
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:55
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionGroupDelete()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }
        $model = AttachmentGroup::findOne([
            'id' => \Yii::$app->request->post('id'),
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$model) {
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '分组已删除。',
            ]);
        }

        switch (\Yii::$app->request->post('type')) {
            case 1:
                $edit = ['is_recycle' => 1];
                break;
            case 2:
                $edit = ['is_recycle' => 0];
                break;
            case 3:
                $edit = ['is_delete' => 1];
                break;
            default:
                throw new \Exception('TYPE 错误');
                break;
        }
        $model->attributes = $edit;
        if (!$model->save()) {
            return $this->asJson((new BaseModel())->responseErrorInfo($model));
        }

        AttachmentInfo::updateAll($edit, [
            'group_id' => $model->id,
            'mall_id' => $mall->id,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    /**
     * 获取当前商城数据
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:55
     * @return Mall
     */
    protected function getMall()
    {
        if ($this->jxMall) {
            return $this->jxMall;
        }
        $id = \Yii::$app->getSessionJxMallId();
        if (!$id) {
            return null;
        }
        $mall = Mall::findOne(['id' => $id]);
        if (!$mall) {
            return null;
        }
        $this->jxMall = $mall;
        return $this->jxMall;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-21
     * @Time: 16:01
     * @Note:多商户ID
     * @return null
     */
    protected function getMchId()
    {
        if ($this->mchId) {
            return $this->mchId;
        }
        /* @var Admin $admin */
        $mchId = !\Yii::$app->user->isGuest ? \Yii::$app->user->identity->mch_id : null;
        $this->mchId = $mchId ? $mchId : null;
        return $this->mchId;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 15:54
     * @Note:删除附件
     * @return string
     */
    public function actionDelete()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }
        $ids = \Yii::$app->request->post('ids');
        if (!is_array($ids)) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提交数据格式错误。',
            ]);
        }
        switch (\Yii::$app->request->post('type')) {
            case 1:
                $edit = ['is_recycle' => 1];
                break;
            case 2:
                $edit = ['is_recycle' => 0];
                break;
            case 3:
                $edit = ['is_delete' => 1];
                break;
            default:
                $edit = ['is_delete' => 1];
                break;
        }
        AttachmentInfo::updateAll($edit, [
            'id' => $ids,
            'mall_id' => $mall->id,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 15:59
     * @Note:文件重命名
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRename()
    {
        $mall = $this->getMall();
        if ($mall) {
            \Yii::$app->setMall($mall);
        }
        $post = \Yii::$app->request->post();
        $attachment = AttachmentInfo::findOne([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'id' => $post['id'],
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$attachment) {
            throw new \Exception('未找到相关附件数据');
        }
        $attachment->name = $post['name'];
        $attachment->save();
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-21
     * @Time: 16:07
     * @Note:移动文件分组
     * @return \yii\web\Response
     */
    public function actionMove()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }

        $ids = \Yii::$app->request->post('ids');
        $groupId = \Yii::$app->request->post('attachment_group_id');

        $attachmentGroup = AttachmentGroup::findOne([
            'id' => $groupId,
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$attachmentGroup) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'data' => '分组不存在，请刷新页面后重试。',
            ]);
        }
        AttachmentInfo::updateAll(['group_id' => $attachmentGroup->id,], [
            'id' => $ids,
            'mall_id' => $mall->id,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }
}