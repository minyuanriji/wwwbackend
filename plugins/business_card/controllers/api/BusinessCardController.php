<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片
 * Author: zal
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\business_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\business_card\BaseController;
use app\plugins\business_card\forms\api\BusinessCardCustomerForm;
use app\plugins\business_card\forms\api\BusinessCardCustomerTeamForm;
use app\plugins\business_card\forms\api\BusinessCardForm;
use app\plugins\business_card\forms\api\BusinessCardPosterForm;
use app\plugins\business_card\forms\api\BusinessCardTagForm;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\forms\common\MessageCommon;
use app\plugins\business_card\models\BusinessCardTrackLog;

class BusinessCardController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 我的名片个人中心
     */
    public function actionIndex()
    {
        $form = new BusinessCardForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getMyBusinessCardIndex());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 名片列表
     */
    public function actionList()
    {
        $form = new BusinessCardForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-09
     * @Time: 17:42
     * @Note: 我的名片预览
     * @return \yii\web\Response
     */
    public function actionMy()
    {
        $form = new BusinessCardForm();
        $form->id = isset($this->requestData["id"]) ? $this->requestData["id"] : 0;
        return $this->asJson($form->my());
    }


    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-07
     * @Time: 17:42
     * @Note: 创建
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        $form = new BusinessCardForm();
        $form->form_data = $this->requestData;
        return $this->asJson($form->create());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-07
     * @Time: 17:42
     * @Note: 编辑
     * @return \yii\web\Response
     */
    public function actionDoEdit()
    {
        $form = new BusinessCardForm();
        $form->form_data = $this->requestData;
        return $this->asJson($form->edit());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-07
     * @Time: 17:42
     * @Note: 编辑详情
     * @return \yii\web\Response
     */
    public function actionToEdit()
    {
        $form = new BusinessCardForm();
        $form->id = isset($this->requestData["id"]) ? $this->requestData["id"] : 0;
        $form->card_token = isset($this->requestData["card_token"]) ? $this->requestData["card_token"] : "";
        return $this->asJson($form->editDetail());
    }

    /**
     * 名片-点赞
     * @return \yii\web\Response
     */
    public function actionLike(){
        $form = new BusinessCardForm();
        $form->num = isset($this->requestData["num"]) ? $this->requestData["num"] : 1;
        $form->id = $this->requestData["id"];
        return $this->asJson($form->like());
    }

    /**
     * 添加标签
     * @return \yii\web\Response
     */
    public function actionAddTag(){
        $form = new BusinessCardTagForm();
        $form->bcid = isset($this->requestData["bcid"]) ? $this->requestData["bcid"] : 0;
        $form->name = isset($this->requestData["name"]) ? $this->requestData["name"] : "";
        return $this->asJson($form->add());
    }

    /**
     * 标签点赞
     * @return \yii\web\Response
     */
    public function actionLikeTag(){
        $form = new BusinessCardTagForm();
        $form->num = isset($this->requestData["num"]) ? $this->requestData["num"] : 1;
        $form->id = $this->requestData["id"];
        return $this->asJson($form->like());
    }

    /**
     * 保存通讯录
     * @return \yii\web\Response
     */
    public function actionSaveAddressBook(){
        $data = $this->requestData;
        $result = BusinessCardTrackLogCommon::addTrackLog($data["track_user_id"],$data["model_id"],BusinessCardTrackLog::TRACK_TYPE_SAVE_MOBILE);
        return $this->asJson($result);
    }

    /**
     * 商机
     * @return \yii\web\Response
     */
    public function actionBusiness(){
        $businessCardCustomerForm = new BusinessCardCustomerForm();
        $businessCardCustomerForm->attributes = $this->requestData;
        $result = $businessCardCustomerForm->add();
        return $this->asJson($result);
    }

    /**
     * 我的线索
     * @return \yii\web\Response
     */
    public function actionMyClue(){
        $businessCardCustomerTeamForm = new BusinessCardCustomerTeamForm();
        $businessCardCustomerTeamForm->attributes = $this->requestData;
        $result = $businessCardCustomerTeamForm->myClue();
        return $this->asJson($result);
    }

    /**
     * 消息中心初始化
     * @return \yii\web\Response
     */
    public function actionMessageInit(){
        $messageCommon = new MessageCommon();
        return $this->asJson($messageCommon->createSignature());
    }

    /**
     * 名片二维码
     * @return array
     */
    public function actionPoster(){
        $form = new BusinessCardPosterForm();
        $form->sign = "business_card/";
        return $form->get();
    }
}