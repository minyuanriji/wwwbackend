<?php
namespace app\mch\forms\mch;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;

class MchAccountModifyForm extends BaseModel{

    public $mall_id;
    public $mch_id;
    public $money;
    public $desc;
    public $type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mall_id', 'mch_id', 'type', 'money', 'desc'], 'required'],
            [['mall_id', 'mch_id', 'type'], 'integer'],
            [['money'], 'number', 'min' => 0],
            [['desc'], 'string']
        ]);
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {
            $accountLog = new MchAccountLog();
            $accountLog->mall_id    = $this->mall_id;
            $accountLog->mch_id     = $this->mch_id;
            $accountLog->money      = $this->money;
            $accountLog->desc       = $this->desc;
            $accountLog->type       = $this->type; //类型：1=收入，2=支出
            $accountLog->created_at = time();
            if (!$accountLog->save()) {
                throw new \Exception($this->responseErrorMsg($accountLog));
            }

            $mch = Mch::findOne($this->mch_id);
            if(!$mch){
                throw new \Exception("商家不存在");
            }

            if($this->type == 1){ //收入
                $mch->account_money += (float)$this->money;
            }else{ //支出
                $mch->account_money -= (float)$this->money;
            }

            if(!$mch->save()){
                throw new \Exception($this->responseErrorMsg($mch));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public static function modify(Mch $mch, $money, $desc, $is_add){
        $form = new static([
            'mall_id' => $mch->mall_id,
            'mch_id'  => $mch->id,
            'type'    => $is_add ? 1 : 2,
            'money'   => floatval($money),
            'desc'    => $desc
        ]);
        return $form->save();
    }

}