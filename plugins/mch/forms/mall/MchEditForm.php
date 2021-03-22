<?php

namespace app\plugins\mch\forms\mall;

use app\component\efps\Efps;
use app\core\ApiCode;
use app\forms\common\template\tplmsg\AudiResultTemplate;
use app\mch\forms\mch\EfpsReviewInfoForm;
use app\models\MchRelatEfps;
use app\models\Model;
use app\models\User;
use app\plugins\mch\forms\common\MchEditFormBase;
use app\plugins\mch\models\Mch;
use app\plugins\wxapp\models\WxappTemplate;

class MchEditForm extends MchEditFormBase
{
    public $is_review;
    public $review_status;
    public $review_remark;
    public $review_info;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['review_remark'], 'string'],
            [['review_status', 'is_review'], 'integer'],
            [['review_info'], 'safe']
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setMch();
            $this->setReviewInfo();
            $this->setStore();
            $this->setMallMchSetting();
            $this->setMchSetting();
            $this->setAdmin();
            $this->setUser();
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
     * 设置审核信息
     * @throws \Exception
     */
    protected function setReviewInfo(){
        if($this->mch->review_status == $this->review_status)
            return;

        $form = new EfpsReviewInfoForm();
        $form->attributes   = $this->review_info;
        $form->mch_id       = $this->mch->id;
        $form->merchantName = $this->name;
        $res = $form->save();
        if($res['code'] != ApiCode::CODE_SUCCESS){
            throw new \Exception($res['msg']);
        }
        $this->setReviewStatus();
    }

    protected function setReviewStatus(){
        if($this->review_status == Mch::REVIEW_STATUS_NOTPASS){ //审核不通过
            MchRelatEfps::updateAll(['status' => 3], ["mch_id" => $this->mch->id]);
            Mch::updateAll([
                "review_status" => Mch::REVIEW_STATUS_NOTPASS,
                "review_remark" => $this->review_remark
            ], ["id" => $this->mch->id]);
        }elseif($this->review_status == Mch::REVIEW_STATUS_CHECKED){ //审核通过

            $reviewData = MchRelatEfps::find()->where(["mch_id" => $this->mch->id])->asArray()->one();

            if(!$reviewData){
                throw new \Exception("无法获取审核信息");
            }

            $params = [
                'register_type' => $reviewData['register_type'],
                'merchantName'  => $reviewData['merchantName'],
                'acceptOrder'   => $reviewData['acceptOrder'],
                'openAccount'   => $reviewData['openAccount']
            ];
            foreach($reviewData as $key => $value){
                if(preg_match("/^(paper_stage_|paper_)(.*)/", trim($key), $match)){
                    $params[$match[2]] = trim($value);
                }
            }

            if(empty($reviewData['acqMerId'])){
                $res = \Yii::$app->efps->merchantApply($params);
                if($res['code'] != Efps::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                MchRelatEfps::updateAll(["acqMerId" => $res['data']['acqMerId']], [
                    "mch_id" => $this->mch->id
                ]);
                $acqMerId = $res['data']['acqMerId'];
            }else{
                $acqMerId = $reviewData['acqMerId'];
            }

            //查询是否审核通过了
            $res = \Yii::$app->efps->merchantQuery(["acqMerId" => $acqMerId]);
            if($res['code'] != Efps::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            //审核通过
            if($res['data']['accountStatus'] == 1 && $res['data']['auditStatus'] == 2){
                MchRelatEfps::updateAll(['status' => 2], ["mch_id" => $this->mch->id]);
                Mch::updateAll(["review_status" => Mch::REVIEW_STATUS_CHECKED], [
                    "id" => $this->mch->id
                ]);
            }
        }
    }

    /**
     * @param Mch $mch
     * @return bool
     * @throws \Exception
     */
    protected function extraMchInfo($mch)
    {
        /*if ($this->is_review) {
            if (!$this->review_status) {
                throw new \Exception('请选择审核状态');
            }

            // 后台操作 商户审核时提交
            if ($this->is_review) {
                $mch->review_status = $this->review_status;
                $mch->review_remark = $this->review_remark;
                $mch->review_time = mysql_timestamp();
            }
        }*/
        return true;
    }

    protected function getMch()
    {
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

    protected function checkData()
    {
        if ($this->bg_pic_url && !is_array($this->bg_pic_url)) {
            throw new \Exception('店铺背景图参数错误');
        }
        if ($this->transfer_rate < 0 || $this->transfer_rate > 1000) {
            throw new \Exception('请填写0~1000数值之间的手续费');
        }
    }
}

