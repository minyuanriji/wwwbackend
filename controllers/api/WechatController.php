<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-22
 * Time: 14:28
 */

namespace app\controllers\api;

use app\controllers\BaseController;
use app\core\ApiCode;
use app\helpers\WechatHelper;
use app\services\wechat\RuleKeywordService;
use app\services\wechat\WechatMessageService;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Text;
use Yii;
use yii\web\NotFoundHttpException;


/** 还未完善
 * Class WechatController
 * @package app\controllers\api
 * @Notes 微信公众号控制器
 */
class WechatController extends ApiController
{

    /**
     * 微信请求关闭CSRF验证
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:28
     * @Note:处理微信消息
     * @return array|mixed
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        switch ($request->getMethod()) {
            // 激活公众号
            case 'GET':
                if (WechatHelper::verifyToken($request->get('signature'), $request->get('timestamp'), $request->get('nonce'))) {
                    return $request->get('echostr');
                }
                throw new NotFoundHttpException('签名验证失败.');
                break;
            // 接收数据
            case 'POST':
                $app = Yii::$app->wechat->getApp();
                $app->server->push(function ($message) {
                    try {
                        switch ($message['MsgType']) {
                            case 'event' : // '收到事件消息';
                                $reply = $this->event($message);
                                break;
                            case 'text' :
                                {
                                    //文字消息
                                    $reply = RuleKeywordService::match($message['Content']);
                                }
                                break;
                            default :
                                {
                                    // ... 其它消息(image、voice、video、location、link、file ...)
                                    $reply = new Text('您好，系统暂不支持接受此类信息');
                                }
                                break;
                        }
                        return $reply;
                    } catch (\Exception $e) {
                        // 记录行为日志
                        if (YII_DEBUG) {
                            return $e->getMessage();
                        }
                        return '系统出错，请联系管理员';
                    }
                });

                // 将响应输出
                $response = $app->server->serve();
                $response->send();
                break;
            default:
                throw new NotFoundHttpException('所请求的页面不存在.');
        }

        exit();
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:28
     * @Note:事件处理
     * @param $message
     * @return bool
     */
    protected function event($message)
    {

        switch ($message['Event']) {
            // 关注事件
            case 'subscribe' :


                break;
            // 取消关注事件
            case 'unsubscribe' :

                return false;
                break;
            // 二维码扫描事件
            case 'SCAN' :

                break;
            // 上报地理位置事件
            case 'LOCATION' :
                //TODO 暂时不处理
                break;
            // 自定义菜单(点击)事件
            case 'CLICK' :
                $content = $message['EventKey'];
                $reply = RuleKeywordService::match($content);
                return $reply;

        }

        return false;
    }

    public function actionJssdkConfig()
    {

        $app = Yii::$app->wechat->getApp();
        $url = Yii::$app->request->get('url');
        if($url){
            $app->jssdk->setUrl($url);
        }

        $res = $app->jssdk->buildConfig([
            'chooseImage',
            'onMenuShareTimeline',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'onMenuShareQZone',
            'updateAppMessageShareData',
            'stopRecord',
            'scanQRCode',
            'chooseWXPay',
            'getLocation',
            'checkJsApi',
            'openAddress'
        ], $debug = false, $beta = false, $json = false);
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '请求成功', 'data' => ['config' => $res]]);

    }


}