<?php
namespace app\forms\api\mall;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Mall;
use app\models\Option;

class CacheMallConfigForm extends BaseModel implements ICacheForm{

    public $stands_mall_id = 0;
    public $http_type;
    public $http_host;
    public $base_url;

    public function rules(){
        return [
            [['http_type', 'http_host', 'stands_mall_id'], 'safe']
        ];
    }

    public function getCacheKey(){
        return [$this->stands_mall_id, $this->http_type, $this->http_host];
    }

    public function getSourceDataForm(){
        $mall_setting = \Yii::$app->mall->getMallSetting();
        $mall_setting['setting']["name"] = $mall_setting["name"];
        $setting['setting'] = $mall_setting['setting'];

        //获取金豆券开启状态
        $optionCache = OptionLogic::get(
            Option::NAME_PAYMENT,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            '',
            0
        );

        $integral_enable = isset($optionCache->integral_status) ? $optionCache->integral_status : 0;

        //获取当前logo
        $mall_logo = '';
        if ($this->stands_mall_id && $this->stands_mall_id != 5) {
            $mal_res = Mall::findOne(['id' => $this->stands_mall_id, 'is_delete' => 0, 'is_recycle' => 0, 'is_disable' => 0]);
            if (!$mal_res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '此商城不存在，请联系客服！',
                ];
            }
            $mall_logo = $mal_res->logo;
        }

        return new APICacheDataForm([
            "sourceData" => [
                'mall_setting'    => $setting,
                'copyright'       => AppConfigLogic::getCoryRight(),
                'cat_style'       => AppConfigLogic::getAppCatStyle(),
                'global_color'    => AppConfigLogic::getColor(),
                'register_agree'  => AppConfigLogic::getRegisterAgree(),
                'integral_enable' => $integral_enable,
                'mall_log'        => $mall_logo
            ]
        ]);
    }

}