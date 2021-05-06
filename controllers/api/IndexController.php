<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:13
 */

namespace app\controllers\api;

use app\forms\api\IndexForm;
use app\forms\mall\data_statistics\TimingStatisticsForm;
use app\models\User;
use app\models\UserInfo;
use function EasyWeChat\Kernel\Support\get_client_ip;

use app\component\aiBaidu\lib\AipSpeech;


class IndexController extends ApiController
{
    public function actionAiBaidu()
    {
        $client = new AipSpeech('24096796', 'w8OTI2ViI8RoX2g3ztDR65Qu', 'Hauf9U6OpVthhkhoIm67gaObhyNm68xg');
        $result = $client->synthesis('商家扫码到账111111', 'zh', 1, array(
            'vol' => 5,
            'per' => 0
        ));

        // 识别正确返回语音二进制 错误则返回json 参照下面错误码
        $a = false;
        if(!is_array($result)){
            $a = file_put_contents('shangjia.mp3', $result);
        }
        echo $a;
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getIndex());
    }


    //时间段调用
    public function actionUpdateHour(){
        return ['msg'=>'注册成功','access_token' => "",'mobile'=>""];
    }
}