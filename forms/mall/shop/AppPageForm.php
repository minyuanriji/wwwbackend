<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 版权新增或编辑表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 11:19
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\forms\common\attachment\CommonUpload;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\AttachmentStorage;
use app\models\BaseModel;
use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\QrCode;
use yii\helpers\FileHelper;

class AppPageForm extends BaseModel
{
    public $path;
    public $params;

    public function rules()
    {
        return [
            [['path', 'params'], 'trim'],
            [['path'], 'required'],
            [['params'], 'safe']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $baseUrl = \Yii::$app->request->hostInfo . '/h5/?mall_id=' . \Yii::$app->mall->id . '#';
        $dir = $this->path . '.jpg';
        $url = $baseUrl . $this->path;
        $file = \Yii::getAlias('@runtime/qrcode/') . $dir;//生成的二维码保存地址
        if (!file_exists($file)) {
            FileHelper::createDirectory(dirname($file));
            $qrCode = (new QrCode($url, ErrorCorrectionLevelInterface::HIGH))
                ->setLogoWidth(60)->setSize(300)->setMargin(5);
            $qrCode->writeFile($file);
        }

        $qrcodeUrl = \Yii::$app->request->hostInfo.'/runtime/qrcode/'.$this->path.'.jpg';

        $qrcodeUrl = CommonLogic::uploadImgToCloudStorage($file,$dir,$qrcodeUrl);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => ['h5_qrcode' => $qrcodeUrl]
        ];
    }
}
