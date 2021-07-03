<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 上传文件公共方法
 * Author: zal
 * Date: 2020-08-28
 * Time: 16:05
 */

namespace app\forms\common\attachment;

use app\core\ApiCode;
use app\forms\common\AttachmentUploadForm;
use app\models\AttachmentInfo;
use app\models\AttachmentStorage;
use app\models\BaseModel;
use app\models\CosSetting;
use app\models\OssSetting;
use app\models\QiniuSetting;
use Qcloud\Cos\Client;
use yii\base\Exception;

class CommonUpload extends BaseModel
{

    public $mall;
    public $user; // 当商城存在时，用户为商城所属的账户；当商城不存在是，用户为传入的用户
    protected $mall_id;
    protected $user_id;
    protected $attachmentStorage;
    public $tempName;
    public $filePath;

    public function save(){
        //获取文件存储位置信息
        $this->attachmentStorage = AttachmentStorage::findOne(['status' => AttachmentStorage::STATUS_ON]);

        $attachmentUploadForm = new AttachmentUploadForm();
        if (!$this->attachmentStorage) {
            $res = $attachmentUploadForm->saveToLocal();
        }
        if ($this->attachmentStorage) {
            $attachmentUploadForm->saveFile = $this->tempName;
            switch ($this->attachmentStorage->type) {
                case 2:
                    $attachmentUploadForm->oss = OssSetting::findOne(['mall_id' => $this->mall_id, 'id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                    if (!$attachmentUploadForm->oss) {
                        return ['code' => ApiCode::CODE_FAIL, 'msg' => '阿里云对象储存配置错误'];
                    }
                    $attachmentUploadForm->savePath = $this->filePath;
                    $res = $attachmentUploadForm->saveToAliOss(1);
                    break;
                case 3:
                    $attachmentUploadForm->cos = CosSetting::findOne(['id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                    if (!$attachmentUploadForm->cos) {
                        return ['code' => ApiCode::CODE_FAIL, 'msg' => '腾讯云对象储存配置错误'];
                    }
                    $attachmentUploadForm->savePath = $this->filePath;
                    $res = $attachmentUploadForm->saveToCos(1);
                    break;
                case 4:
                    $attachmentUploadForm->qiniu = QiniuSetting::findOne(['mall_id' => $this->mall_id, 'id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                    if (!$attachmentUploadForm->qiniu) {
                        return ['code' => ApiCode::CODE_FAIL, 'msg' => '七牛云对象储存配置错误'];
                    }
                    $attachmentUploadForm->savePath = $this->filePath;
                    $res = $attachmentUploadForm->saveToQiniu(1);
                    break;
                default:
                    $res = $attachmentUploadForm->saveToLocal();
                    break;
            }
        }

        if ($res) {
            $type = strtolower(pathinfo($this->tempName, PATHINFO_EXTENSION));
            if($type != "video"){
                $attachment = new AttachmentInfo();
                $attachment->mall_id = empty($this->mall_id) ? 0 : $this->mall_id;
                $attachment->admin_id = empty(\Yii::$app->user->id) ? 0 : \Yii::$app->user->id;
                $attachment->url = $res['url'];
                $attachment->thumb_url = $res['thumb_url'];
                $attachment->type = strtolower(pathinfo($this->tempName, PATHINFO_EXTENSION));
                $attachment->created_at = time();
                $attachment->name = $res['name'];
                $attachment->size = filesize($this->tempName);
                $attachment->group_id = 0;
                $attachment->from = 2;
                if (!$attachment->save()) {
                    return ['code' => ApiCode::CODE_FAIL, 'msg' => '上传失败', 'error' => $attachment->getErrors()];
                }
            }
        }
        return $res;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-03
     * @Time: 17:59
     * @Note:腾讯云COS
     * @return array|bool
     */
    public function saveToCos()
    {
        $region = $this->cos->region;
        $bucket = $this->cos->bucket;
        $secretKey = $this->cos->secret_key;
        $secretId = $this->cos->secret_id;
        $client = new Client([
            'region' => $region,
            'credentials' => [
                'secretKey' => $secretKey,
                'secretId' => $secretId
            ]
        ]);
        $key = trim($this->savePath . $this->saveName, '/');
        /** @var \GuzzleHttp\Command\Result $result */
        //
        try {
            $result = $client->upload($bucket, $key, fopen($this->file->tempName, 'rb'));
            //腾讯云对象存储报错
            if ($result) {
                $this->url = "http://" . $result['Location'];
                return ['url' => $this->url, 'extension' => $this->file->getExtension(), 'size' => $this->file->size, 'thumb_url' => $this->url, 'type' => $this->type, 'name' => $this->file->name];
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
}