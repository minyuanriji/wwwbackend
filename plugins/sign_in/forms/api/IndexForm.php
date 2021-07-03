<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件接口首页表单类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\api;

use app\core\ApiCode;
use app\forms\common\template\TemplateList;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\Coupon;
use app\plugins\sign_in\models\SignInAwardConfig;
use app\plugins\sign_in\models\SignInUser;

class IndexForm extends ApiModel
{
    public $month;

    public function rules()
    {
        return [
            [['month'], 'integer']
        ];
    }

    /**
     * 搜索
     * @return array
     */
    public function search()
    {
        try {
            $common = Common::getCommon($this->mall);

            $config = $common->getConfig();
            if (!$config || $config['status'] != 1) {
                throw new \Exception('未开启签到');
            }

            if (isset($this->attributes['month']) && !empty($this->attributes['month'])){
                $month = strtotime($this->attributes['month']);
                $firstDay = date('Y-m-01', $month);
            }else{
                $firstDay = date('Y-m-01', time());
            }
            //用户已签到日期
            $signInDay = $common->getUserSignInList($this->user,$firstDay);

            $newList = [];
            foreach ($config as $key => $item) {
                $ignore = ['id','is_remind','time', 'mall_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'continue_type'];
                if (in_array($key, $ignore)) {
                    continue;
                }
                $newList[$key] = $item;
            }

            // 获取普通签到奖励信息
            $normal = $common->getAwardConfigNormalNew(['status'=>1]);
            $newList['normal'] = $this->ruleList($normal);
            //获取用连续签到奖励
            //优惠券
            $successive = $common->getAwardConfigNormalNew(['status'=>2]);
            $newList['successive'] = $this->ruleList($successive);

            $signInUser = $this->signInList($this->user);

            $continueDay = 0;
            if($signInDay){
                foreach($signInDay as $item){
                    if($item['is_sign']){
                        $continueDay += 1;
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'is_sign_in' => $signInUser['is_sign_in'],
                    //'continue_day' => $signInUser['continue_day'],
                    'continue_day' => $continueDay,
                    'config' => $newList,
                    'sign_in_day' => $signInDay,
                    //'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['sign_in_tpl'])
                ]
            ];


        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    //获取签到详情
    public function signInList($user){
        if (!$user){
            return [
                'is_sign_in'   => false,
                'continue_day' => 0,
            ];
        }
        //查看今天是否签到
        $signInUser = new SignInUser();
        //查看是否签过到了
        $common = Common::getCommon($this->mall);
        $config = $common->getConfig();
        $data['is_sign_in'] = false;
        $data['continue_day'] = 0;

        $startTime = strtotime(date("Y-m-d",time()));

        $isSignIn = $signInUser->getUserSignInStatus($this->user->id,$startTime,time());

        if (!empty($isSignIn)){
            $data['is_sign_in'] = true;
            $data['continue_day'] = $isSignIn['continue'];
        }else{
            $ydTime = strtotime(date("Y-m-d",(time()-(60*60*24))));
            $isSignIn = $signInUser->getUserSignInStatus($this->user->id,$ydTime,strtotime(date("Y-m-d",(time())))-1);
            if (!empty($isSignIn) && $config->config_at < $isSignIn['created_at']){
                $data['continue_day'] = $isSignIn['continue'];
            }
        }
        return $data;
    }




    //组合数组，加上优惠券名称，并查看是否领取奖励
    public function ruleList($ruleList,$userId = ''){

        //去除优惠券id然后去除重复项，去除空数组
        $couponIdList = array_filter(array_unique(array_column($ruleList,'coupon_id')));

        //查询优惠券名称
        $couponNameList = [];
        if (!empty($couponIdList)){
            $couponModel = new Coupon();
            $common = Common::getCommon($this->mall);
            $couponNameList = $couponModel->getCouponList($couponIdList,'id,name');
            $couponNameList = $common->arrayUnderReset($couponNameList,'id');
        }


        $list = [];
        foreach ($ruleList as $item){
            $item['coupon_name'] = '';
            //查看是否为优惠券
            if ($item['type'] == SignInAwardConfig::TYPE_COUPON){
                if (isset($couponNameList[$item['coupon_id']])){
                    $item['coupon_name'] = $couponNameList[$item['coupon_id']]['name'];
                }else{
                    unset($item);
                    continue;
                }
                $item['number'] = floor($item['number']);
            }
            $list[] = $item;

        }
        return $list;
    }

    /**
     * 获取签到天数
     * @return array
     */
    public function getDay()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->user) {
                $common = Common::getCommon($this->mall);
                $signInDay = $common->getDay($this->month, $this->user);
            } else {
                $signInDay = [];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'sign_in_day' => $signInDay
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 获取签到配置
     * @return array
     */
    public function getCustomize()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $config = Common::getCommon(\Yii::$app->mall)->getCustomize();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $config
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
