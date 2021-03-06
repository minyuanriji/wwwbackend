<?php
namespace app\mch\forms\common;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\logic\VideoLogic;
use app\models\AttachmentInfo;
use app\models\AttachmentStorage;
use app\models\CosSetting;
use app\models\OssSetting;
use app\models\QiniuSetting;
use app\plugins\business_card\models\BusinessCardSetting;
use Grafika\Grafika;
use Grafika\ImageInterface;
use OSS\Core\OssException;
use OSS\OssClient;
use Qcloud\Cos\Client;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UploadedFile;

class AttachmentUploadForm extends Model
{
    /** @var UploadedFile */
    public $file;
    public $savePath;
    public $saveThumbFolder;//缩略图
    public $saveName;
    public $url;
    public $thumb_url;
    public $baseWebUrl;
    public $baseWebPath;
    public $saveFile;
    public $type;//文件类型
    protected $docExt = ['txt', 'docx', 'doc', 'pptx', 'ppt', 'xls', 'csv', 'pdf', 'md', 'xlsx', 'pem', 'txt'];
    protected $imageExt = ['jpg', 'bmp', 'png', 'gif', 'jpeg', 'webp',];
    protected $videoExt = ['mp4', 'ogg','mov'];
    public $mall_id;
    public $admin_id;
    public $mch_id;
    public $upload_setting;
    public $attachmentStorage;
    public $maxSize = 10;
    public $from; //来源1后台2前台
    /**
     * @var OssSetting $oss
     */
    public $oss;
    /**
     * @var CosSetting $cos
     */
    public $cos;
    /**
     * @var QiniuSetting $qiniu
     */
    public $qiniu;

    public $group_id;

    public function rules()
    {
        return [
            [['file'], 'file'],
            [['file'], 'validateFileSize'],
            [['file'], 'validateFileExt'],
            [['group_id'], 'integer'],
            [['group_id'], 'default', 'value' => 0]
        ]; // TODO: Change the autogenerated stub
    }

    /**
     * 验证文件是否受支持
     * @param $a
     * @return bool
     */
    public function validateFileExt($a){
        $allSupportExt = array_merge($this->docExt, $this->imageExt, $this->videoExt);
        if (!in_array($this->file->getExtension(), $allSupportExt)) {
            return false;
        }
        return true;
    }

    /**
     * 验证视频文件大小
     * @param $a
     * @return bool
     */
    public function validateFileSize($a){
        if (in_array($this->file->getExtension(), $this->videoExt)) {
            try{
                $businessCardSetting = BusinessCardSetting::getData($this->mall_id);
                if(isset($businessCardSetting[BusinessCardSetting::VIDEO_SIZE])){
                    $videoSize = $businessCardSetting[BusinessCardSetting::VIDEO_SIZE];
                    $this->maxSize = $videoSize;
                }
            }catch (\Exception $ex){

            }
            $videoSize = $this->maxSize * 1024  * 1024;
            if ($this->file->size > $videoSize) {
                return false;
            }
        }
        return true;
    }


    /**
     * 文件数据保存
     * @return array
     * @throws \Exception
     */
    public function save(){

        if (!$this->validateFileExt("file")) {
            $msg = '不支持' . $this->file->getExtension() . '的文件！';
            if (in_array($this->file->getExtension(), $this->videoExt)) {
                $msg = "视频仅支持 ".join($this->videoExt)." 格式";
            }
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $msg];
        }

        if (!$this->validateFileSize("file")) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '视频大小不超过'.$this->maxSize."mb"];
        }

        $dateFolder = date('Ymd');
        if (in_array($this->file->getExtension(), $this->imageExt)) {
            $this->type = 'image';
            $this->savePath = '/uploads/images/original/' . $dateFolder . '/';
        }
        if (in_array($this->file->getExtension(), $this->videoExt)) {
            $this->type = 'video';
            $this->savePath = '/uploads/video/original/' . $dateFolder . '/';
        }
        if (in_array($this->file->getExtension(), $this->docExt)) {
            $this->type = 'doc';
            if($this->file->getExtension()=='pem'){
                $this->savePath = '/uploads/doc/original/cert/mall_'.($this->mall_id?$this->mall_id:0).'/'.date('YmdHis').'/';
            }else{
                $this->savePath = '/uploads/doc/original/' . $dateFolder . '/';
            }
        }
        $this->baseWebPath = \Yii::$app->basePath . '/web';
        $this->baseWebUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
        if ($this->file->getExtension() == 'pem') {
            $this->saveName = $this->file->name;
        } else {
            $this->saveName = md5_file($this->file->tempName) . '.' . $this->file->getExtension();
        }

        $this->saveFile = $this->baseWebPath . $this->savePath . $this->saveName;

        //保存到本地
        $this->attachmentStorage = AttachmentStorage::findOne(['admin_id' => $this->admin_id, 'status' => AttachmentStorage::STATUS_ON]);

        if ($this->file->getExtension() == 'pem') {
            $res = $this->saveToLocal();
        } else {
            if (!$this->attachmentStorage) {
                $res = $this->saveToLocal();
            }
            if ($this->attachmentStorage) {
                switch ($this->attachmentStorage->type) {
                    case 2:
                        $this->oss = OssSetting::findOne(['mall_id' => $this->mall_id, 'admin_id' => $this->admin_id, 'id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                        if (!$this->oss) {
                            return ['code' => ApiCode::CODE_FAIL, 'msg' => '阿里云对象储存配置错误'];
                        }
                        $res = $this->saveToAliOss();
                        break;
                    case 3:
                        //'mall_id' => $this->mall_id
                        $this->cos = CosSetting::findOne(['admin_id' => $this->admin_id, 'id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                        if (!$this->cos) {
                            return ['code' => ApiCode::CODE_FAIL, 'msg' => '腾讯云对象储存配置错误'];
                        }
                        $res = $this->saveToCos();
                        break;
                    case 4:
                        $this->qiniu = QiniuSetting::findOne(['mall_id' => $this->mall_id, 'admin_id' => $this->admin_id, 'id' => $this->attachmentStorage->setting_id, 'is_delete' => 0]);
                        if (!$this->qiniu) {
                            return ['code' => ApiCode::CODE_FAIL, 'msg' => '七牛云对象储存配置错误'];
                        }
                        $res = $this->saveToQiniu();
                        break;
                    default:
                        $res = $this->saveToLocal();
                        break;
                }
            }
        }

        if ($res) {
            if($this->type == "video"){
                $fileName = 'video_'.date("YmdHis").'.jpg';
                $path = $this->baseWebPath.'/uploads/video/original/';
                $filePath = '/uploads/video/original/'.$fileName;
                $saveViedoImg = $path.$fileName;
                $url = $this->baseWebUrl.$filePath;
                \Yii::warning("attachmentUploadForm save saveViedoImg=".$saveViedoImg);
                //截取视频图片，并上传到第三方图片存储平台
                VideoLogic::getVideoImage($res['url'],$saveViedoImg);
                $imgUrl = CommonLogic::uploadImgToCloudStorage($saveViedoImg,$filePath,$url);
                $res['thumb_url'] = $imgUrl;
            }
            $attachment = new AttachmentInfo();
            $attachment->mall_id    = empty($this->mall_id) ? 0 : $this->mall_id;
            $attachment->admin_id   = $this->admin_id;
            $attachment->mch_id     = $this->mch_id;
            $attachment->url        = $res['url'];
            $attachment->thumb_url  = $res['thumb_url'];
            $attachment->type       = $this->type;
            $attachment->created_at = time();
            $attachment->name       = $res['name'];
            $attachment->size       = $this->file->size;
            $attachment->group_id   = $this->group_id;
            $attachment->from       = $this->from;
            if (!$attachment->save()) {
                return ['code' => ApiCode::CODE_FAIL, 'msg' => '上传失败', 'error' => $attachment->getErrors()];
            }
        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => 'success', 'data' => $res];
    }

    /**
     * 保存到本地
     * @return array
     * @throws \Exception
     */
    public function saveToLocal(){
        $dateFolder = date('Ymd');
        $this->url = $this->baseWebUrl . "/" . $this->savePath . $this->saveName;
        if (!is_dir($this->baseWebPath . $this->savePath)) {
            if (!make_dir($this->baseWebPath . $this->savePath)) {
                throw new \Exception('上传失败，创建文件夹失败`'
                    . $this->baseWebPath
                    . $this->savePath
                    . '`，请检查目录写入权限。');
            }
        }
        if (!$this->file->saveAs($this->saveFile)) {
            if (!copy($this->file->tempName, $this->saveFile)) {
                throw new \Exception('文件保存失败，请检查目录写入权限。');
            }
        }
        $this->saveThumbFolder = '/uploads/images/thumbs/' . $dateFolder . '/';
        if ($this->type == 'image') {
            $saveThumbName = $this->baseWebPath . $this->saveThumbFolder . $this->saveName;
            if (!is_dir($this->baseWebPath . $this->saveThumbFolder)) {
                if (!make_dir($this->baseWebPath . $this->saveThumbFolder)) {
                    throw new \Exception('上传失败，创建文件夹失败`'
                        . $this->baseWebPath
                        . $this->saveThumbFolder
                        . '`，请检查目录写入权限。');
                }
            }
            //裁剪图片存入本地
            $editor = Grafika::createEditor(get_supported_image_lib());
            /**
             * @var ImageInterface $image
             */
            $editor->open($image, $this->saveFile);
            $editor->resizeFit($image, 200, 200);
            $editor->save($image, $saveThumbName);
            $this->thumb_url = $this->baseWebUrl . $this->saveThumbFolder . $this->saveName;
        }

        if ($this->file->getExtension() == 'pem') {
            $this->thumb_url = $this->savePath . $this->file->name;
            $this->url = $this->savePath . $this->file->name;
        }
        return ['url' => $this->url, 'extension' => $this->file->getExtension(), 'size' => $this->file->size, 'thumb_url' => $this->thumb_url, 'type' => $this->type, 'name' => $this->file->name];
    }

    /**
     * 这里使用阿里云上传，上传失败返回false
     * @param $flag 0 页面上传 1 本地现有图片上传
     * @return array|bool
     */
    public function saveToAliOss($flag){
        $accessKey = $this->oss->access_key;
        $accessSecret = $this->oss->access_secret;
        $bucketName = $this->oss->bucket;
        $endPoint = $this->oss->end_point;
        $res = null;
        $object = trim($this->savePath . $this->saveName, '/');
        if($flag){
            $tempName = $this->saveFile;
            $object = $this->savePath;
            $extension = "";
            $size = filesize($tempName);
            $name = basename($this->savePath);
            $type = "";
        }else{
            $tempName = $this->file->tempName;
            $extension = $this->file->getExtension();
            $size = $this->file->size;
            $name = $this->file->name;
            $type = $this->type;
        }

        try {
            $ossClient = new OssClient($accessKey, $accessSecret, $endPoint, false);
            $res = $ossClient->uploadFile($bucketName, $object, $tempName);
        } catch (OssException $e) {

        }
        if ($res) {
            $this->url = $res['info']['url'];
            return ['url' => $this->url, 'extension' => $extension, 'size' => $size, 'thumb_url' => $this->url, 'type' => $type, 'name' => $name];
        }
        return false;
    }

    /**
     * 腾讯云COS
     * @param $flag 0 页面上传 1 本地现有图片上传
     * @return array|bool
     */
    public function saveToCos($flag = 0){
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
            if($flag){
                $tempName = $this->saveFile;
                $key = $this->savePath;
                $extension = "";
                $size = filesize($tempName);
                $name = basename($this->savePath);
                $type = "";
            }else{
                $tempName = $this->file->tempName;
                $extension = $this->file->getExtension();
                $size = $this->file->size;
                $name = $this->file->name;
                $type = $this->type;
            }
            $result = $client->upload($bucket, $key, fopen($tempName, 'rb'));
            //腾讯云对象存储报错
            if ($result) {
                $this->url = "http://" . $result['Location'];
                return ['url' => $this->url, 'extension' => $extension, 'size' => $size, 'thumb_url' => $this->url, 'type' => $type, 'name' => $name];
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * 上传到七牛云
     * @param $flag 0 页面上传 1 本地现有图片上传
     * @return array|bool
     * @throws \Exception
     */
    public function saveToQiniu($flag = 0){
        $accessKey = $this->qiniu->access_key;
        $accessSecret = $this->qiniu->access_secret;
        $bucket = $this->qiniu->bucket;
        $domain = $this->qiniu->domain;
        $key = trim($this->savePath . $this->saveName, '/');
        if($flag){
            $tempName = $this->saveFile;
            $key = $this->savePath;
            $extension = "";
            $size = filesize($tempName);
            $name = basename($this->savePath);
            $type = "";
        }else{
            $tempName = $this->file->tempName;
            $extension = $this->file->getExtension();
            $size = $this->file->size;
            $name = $this->file->name;
            $type = $this->type;
        }

        $uploadManager = new UploadManager();
        $auth = new Auth($accessKey, $accessSecret);
        $token = $auth->uploadToken($bucket);

        list($result, $error) = $uploadManager->putFile($token, $key, $tempName);
        if ($result) {
            $this->url = $domain . $result['key'];
            return ['url' => $this->url, 'extension' => $extension, 'size' => $size, 'thumb_url' => $this->url, 'type' => $type, 'name' => $name];
        } else {
            return false;
        }
    }

    public static function getInstanceFromFile($localFilePath){
        if (!is_string($localFilePath)) {
            throw new \Exception('文件名称不是字符串。');
        }
        if (!file_exists($localFilePath)) {
            throw new \Exception('文件`' . $localFilePath . '`不存在。');
        }
        $localFilePath = str_replace('\\', '/', $localFilePath);
        $pathInfo = pathinfo($localFilePath);
        $name = $pathInfo['basename'];
        $size = filesize($localFilePath);
        $type = mimetype_from_filename($localFilePath);
        return new UploadedFile([
            'name'      => $name,
            'type'      => $type,
            'tempName'  => $localFilePath,
            'error'     => 0,
            'size'      => $size,
        ]);
    }
}