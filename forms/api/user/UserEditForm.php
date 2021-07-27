<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车api-购物车操作
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\user;

use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\core\ApiCode;
use app\models\Cart;
use app\models\GoodsAttr;
use app\models\User;
use app\validators\PhoneNumberValidator;

class UserEditForm extends BaseModel
{
    public $avatar;
    public $nickname;
    public $birthday;
    public $mobile;
    public $captcha;

    public function rules()
    {
        return [
            [['nickname','avatar','captcha'],'string'],
            [['birthday'], 'safe'],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }

    /**
     * 修改
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 14:33
     * @return array
     */
    public function edit()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            $user_id = \Yii::$app->user->id;
            /** @var User $user */
            $user = User::getOneData([
                'id' => $user_id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            //是否更新
            $isUpdate = false;
            if(empty($user)){
                throw new \Exception("用户不存在");
            }
            if(!empty($this->nickname)){
                $isUpdate = true;
                $user->nickname = $this->nickname;
            }
            if(!empty($this->birthday)){
                $isUpdate = true;
                $user->birthday = strtotime($this->birthday);
            }
            if(!empty($this->avatar)){
                $isUpdate = true;
                $user->avatar_url = $this->avatar;
            }
            if(!empty($this->mobile)){
                $smsForm = new SmsForm();
                $smsForm->captcha = $this->captcha;
                $smsForm->mobile = $this->mobile;
                if(!$smsForm->checkCode()){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
                }
                if($user['mobile'] == $this->mobile){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,'不必重复绑定同一号码');
                }
                $check = User::find()->select('id')->where(['and',['=','mobile',$this->mobile],['<>','id',$user_id],['=','mall_id',\Yii::$app->mall->id]])->asArray()->one();
                if($check){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,'已经有其他用户绑定该号码');
                }
                $isUpdate = true;
                $user->mobile = $this->mobile;
            }
            $code = ApiCode::CODE_SUCCESS;
            if($isUpdate){
                if($user->save() === false){
                    $code = ApiCode::CODE_FAIL;
                }
            }
            return $this->returnApiResultData($code,"");
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
