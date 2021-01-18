<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 系统二维码
 * Author: zal
 * Date: 2020-04-18
 * Time: 15:50
 */

namespace app\plugins\mpwx\forms;

use app\core\ApiCode;
use app\core\cloud\CloudNotLoginException;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\mpwx\models\MpwxConfig;
use app\plugins\mpwx\models\WxappJumpAppid;

class AppUploadForm extends BaseModel
{
    public $action;
    public $branch;

    public function rules()
    {
        return [
            ['action', 'required'],
            ['branch', 'safe'],
        ];
    }

    public function getResponse()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        try {
            switch ($this->action) {
                case 'login':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->login(),
                    ];
                    break;
                case 'preview':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->preview(),
                    ];
                    break;
                case 'upload':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->upload(),
                    ];
                    break;
                default:
                    break;
            }
        } catch (CloudNotLoginException $exception) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage(),
                'data' => [
                    'retry' => 1,
                ],
            ];
        } catch (\Exception $exception) {
            $msg = $exception->getMessage();
            if (mb_stripos($msg, 'PORT_NOT_EXIST') !== false) {
                $this->unsetToken();
            }
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function login()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
        ];
        $this->setJumpApppid($data);
        return \Yii::$app->cloud->wxapp->login($data);
    }

    public function preview()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
        ];

        $this->setJumpApppid($data);
        return \Yii::$app->cloud->wxapp->preview($data);
    }

    public function upload()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
        ];
        $this->setJumpApppid($data);
        $res = \Yii::$app->cloud->wxapp->upload($data);
        $res['version'] = app_version();
        return $res;
    }

    private function getApiRoot()
    {
        return \Yii::$app->request->scriptUrl . '?_mall_id=' . \Yii::$app->mall->id;
    }

    private function getAppId()
    {
        $wxappConfig = MpwxConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$wxappConfig) {
            throw new \Exception('小程序信息尚未配置。');
        }
        if (!$wxappConfig->appid) {
            throw new \Exception('小程序AppId尚未配置。');
        }
        return $wxappConfig->appid;
    }

    private function getToken()
    {
        $key = 'WXAPP_UPLOAD_TOKEN';
        $token = \Yii::$app->session->get($key);
        if (!$token) {
            $token = \Yii::$app->security->generateRandomString();
            \Yii::$app->session->set($key, $token);
        }
        return $token;
    }

    private function unsetToken()
    {
        $key = 'WXAPP_UPLOAD_TOKEN';
        \Yii::$app->session->remove($key);
    }

    private function getProtocol()
    {
        return 'https';
    }

    private function setJumpApppid(&$data)
    {
        $list = WxappJumpAppid::find()->where(['mall_id' => \Yii::$app->mall->id])->all();

        $newList = [];
        foreach ($list as $index => $item) {
            $newList[] = $item->appid;
            $data['jump_appid_list[' . $index . ']'] = $item->appid;
        }
    }
}
