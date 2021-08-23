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
use app\forms\api\mall\CacheMallConfigForm;
use app\forms\api\user\UserForm;
use app\helpers\APICacheHelper;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\Mall;
use app\models\Option;
use app\models\Wechat;
use app\plugins\mpwx\models\MpwxConfig;

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
        $form = new CacheMallConfigForm();
        $form->attributes     = $this->requestData;
        $form->stands_mall_id = isset($headers['x-stands-mall-id']) ? $headers['x-stands-mall-id'] : 0;
        $form->http_type      = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $form->http_host      = $_SERVER['HTTP_HOST'];
        $form->base_url       = \Yii::$app->request->baseUrl;

        $userCenter = AppConfigLogic::getUserCenter(1);
        $top_pic_url = $form->http_type . $form->http_host . "/web/statics/img/app/top_background_pic.jpg";
        if(!empty($userCenter) && isset($userCenter["top_pic_url"])){
            $top_pic_url = $userCenter["top_pic_url"];
        }

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res['data'] = array_merge($res['data'], [
                "page_title"  => AppConfigLogic::getPageTitleConfig(),
                "navbar"      => AppConfigLogic::getNavbar(),
                'top_pic_url' => $top_pic_url
            ]);
        }

        return $this->asJson($res);
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
     * @Note:获取客服接入链接
     *
     */
    public function actionKeFuLink()
    {
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => "https://tb.53kf.com/code/client/78a1d7f63ae00f9fe02de653ca2024d68/1",
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
     * @Note:用户中心配置
     */
    public function actionUserCenterConfig()
    {
        $form = new UserForm();
        $config = $form->getUserCenterConfig();
        return $config;
    }


}

