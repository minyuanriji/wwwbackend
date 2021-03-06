<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:39
 */

namespace app\controllers\api;

use app\core\ApiCode;
use app\forms\api\user\UserForm;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\Option;

/**
 * Class DefaultController
 * @package app\controllers\api
 * @Notes 商城控制器
 */
class MallController extends ApiController
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 16:26
     * @Note:商城设置
     */
    public function actionMallConfig()
    {
        $mall_setting = \Yii::$app->mall->getMallSetting();
        $mall_setting['setting']["name"] = $mall_setting["name"];
        $setting['setting'] = $mall_setting['setting'];
        $pageTitle = AppConfigLogic::getPageTitleConfig();
        $navbar = AppConfigLogic::getNavbar();

        $userCenter = AppConfigLogic::getUserCenter(1);

        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $top_pic_url = $http_type.$_SERVER['HTTP_HOST']."/web/statics/img/app/top_background_pic.jpg";
        if(!empty($userCenter) && isset($userCenter["top_pic_url"])){
            $top_pic_url = $userCenter["top_pic_url"];
        }

        //获取购物券开启状态
        $optionCache = OptionLogic::get(
            Option::NAME_PAYMENT,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            '',
            0
        );

        $integral_enable = isset($optionCache->integral_status) ? $optionCache->integral_status : 0;

        return $this->asJson([
            'code' => 0,
            'data' => [
                'mall_setting' => $setting,
                'navbar' => $navbar,
                'copyright' => AppConfigLogic::getCoryRight(),
                'cat_style' => AppConfigLogic::getAppCatStyle(),
                'page_title' => $pageTitle,
                'global_color' => AppConfigLogic::getColor(),
                'top_pic_url' => $top_pic_url,
                'register_agree' => AppConfigLogic::getRegisterAgree(),
                'integral_enable' =>$integral_enable,
            ],
        ]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 16:36
     * @Note:获取底部导航栏
     *
     */
    public function actionNavbarConfig()
    {
        $navbar = AppConfigLogic::getNavbar();
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => $navbar,
        ]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 16:56
     * @Note:商城首页配置
     */
    public function actionIndexConfig()
    {
        $mall_setting = \Yii::$app->mall->getMallSetting();
        $setting['setting'] = $mall_setting['setting'];
        $pageTitle = AppConfigLogic::getPageTitleConfig();
        $navbar = AppConfigLogic::getNavbar();
        return $this->asJson([
            'code' => 0,
            'data' => [
                'mall_setting' => $setting,
                'navbar' => $navbar,
                'copyright' => AppConfigLogic::getCoryRight(),
                'cat_style' => AppConfigLogic::getAppCatStyle(),
                'page_title' => $pageTitle,
            ],
        ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:09
     * @Note:用户中心配置
     */
    public function actionUserCenterConfig()
    {
        $form = new UserForm();
        $config = $form->getUserCenterConfig();
        return $config;
    }


}