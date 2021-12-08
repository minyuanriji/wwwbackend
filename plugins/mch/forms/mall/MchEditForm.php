<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\forms\common\template\tplmsg\AudiResultTemplate;
use app\models\EfpsMchReviewInfo;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\plugins\mch\forms\common\MchEditFormBase;
use app\plugins\mch\models\Mch;
use app\plugins\wxapp\models\WxappTemplate;

class MchEditForm extends MchEditFormBase
{
    public $is_review;
    public $review_status;
    public $review_remark;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['review_remark'], 'string'],
            [['review_status', 'is_review'], 'integer'],
            [['review_info'], 'safe']
        ]);
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setMch();
            $this->setStore();
            $this->setMallMchSetting();
            $this->setMchSetting();
//            $this->setAdmin();
            $this->setUser();
            $this->setSettle();
            $this->sendTemplateMsg();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * @param Mch $mch
     * @return bool
     * @throws \Exception
     */
    protected function extraMchInfo($mch){
        return true;
    }

    protected function getMch(){
        if ($this->id) {
            $mch = Mch::findOne(['id' => $this->id, 'is_delete' => 0]);
            if (!$mch) {
                throw new \Exception('商户不存在,ID:' . $this->id);
            }
        } else {
            $mch = new Mch();
            $mch->mall_id = \Yii::$app->mall->id;
            $mch->review_status = 1;
            $mch->review_remark = '后台添加,操作用户:';
            $mch->review_time = mysql_timestamp();
            $mch->form_data = \Yii::$app->serializer->encode([]);

            $this->review_status = 1;
            $this->review_remark = '创建新商户';
        }

        return $mch;
    }

    /**
     * 设置结算信息
     * @throws \Exception
     */
    protected function setSettle(){

        $store = Store::find()->where([
            "mch_id" => $this->mch->id
        ])->orderBy("is_default DESC")->one();

        $efpsReview = EfpsMchReviewInfo::findOne(["mch_id" => $this->mch->id]);
        if(!$efpsReview){
            $efpsReview = new EfpsMchReviewInfo([
                "mch_id"                  => $this->mch->id,
                "created_at"              => time(),
                "merchantName"            => $store->name,
                "status"                  => 2, //审核成功
                "register_type"           => "separate_account",
                "openAccount"             => 1,
                "paper_merchantType"      => 3,
                "paper_isCc"              => 1, //3证合一
                "paper_settleAccountType" => 2,
                "paper_settleTarget"      => 2, //手动提现
                "paper_settleAccountType" => 2,
                "paper_businessCode"      => "WITHDRAW_TO_SETTMENT_DEBIT",
                "is_delete"               => 0
            ]);
        }

        $efpsReview->paper_openBank = $this->settle_bank; //开户银行
        $efpsReview->paper_settleAccountNo = $this->settle_num;
        $efpsReview->paper_settleAccount = $this->settle_realname;
        $efpsReview->updated_at = time();

        if(!$efpsReview->save()){
            throw new \Exception($this->responseErrorMsg($efpsReview));
        }
    }

    private function sendTemplateMsg()
    {
        try {
            $user = User::findOne($this->user_id);
            if (!$user) {
                throw new \Exception('用户不存在！,商户审核订阅消息发送失败');
            }

            $auditResultTemplate = new AudiResultTemplate([
                'remark' => $this->review_remark,
                'result' => $this->review_status == 1 ? '商户审核通过' : '商户审核不通过',
                'name' => $user->nickname,
                'time' => mysql_timestamp()
            ]);

            $auditResultTemplate->page = 'pages/index/index';
            $auditResultTemplate->user = $user;
            $res = $auditResultTemplate->send();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    protected function checkData(){
        if ($this->bg_pic_url && !is_array($this->bg_pic_url)) {
            throw new \Exception('店铺背景图参数错误');
        }
        if ($this->transfer_rate < 0 || $this->transfer_rate > 1000) {
            throw new \Exception('请填写0~1000数值之间的手续费');
        }
    }


}

