<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 公共处理类
 * Author: zal
 * Date: 2020-04-27
 * Time: 14:36
 */

namespace app\logic;

use app\forms\common\attachment\CommonUpload;
use app\models\AttachmentStorage;
use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\QrCode;
use jianyan\easywechat\Wechat;
use yii\helpers\FileHelper;

class CommonLogic
{

    /**
     * 组装异常消息
     * @param \Exception $ex
     * @param string $message
     * @return string
     */
    public static function getExceptionMessage($ex, $message = "")
    {
        $file = $ex->getFile();
        $line = $ex->getLine();
        $message = $message != "" ? $message : $ex->getMessage();
        if (YII_ENV_DEV) {
            $message = "在文件:{$file}的第{$line}行，发生错误:{$message}";
        }
        return $message;
    }

    /**
     * 判断是否是json格式
     * @param type $json_str
     * @return type
     */
    public static function analyJson($json)
    {
        $result = true;
        if (is_string($json) && !empty($json)) {
            json_decode($json);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return $result;
    }

    /**
     * 数组去重
     * @param $arr
     * @param $key
     * @param $tmp
     */
    public static function removalRepeatArrayByKey($arr, $key, &$tmp)
    {
        $tmp_arr = [];
        foreach ($arr as $k => $item) {
            if (in_array($item[$key], $tmp_arr)) {
                $re = array_search($item[$key], $tmp_arr);
                unset($tmp_arr[$re]);
            }
            $tmp_arr[$k] = $item[$key];
        }

        foreach ($tmp_arr as $k => $item) {
            if (array_key_exists($k, $arr)) {
                $tmp[] = $arr[$k];
            }
        }
    }

    /**
     * 去除不用的数组key
     * @param $array
     * @param array $keys ,如果key含有all，则默认去除新增删相关字段
     * @return mixed
     */
    public static function unsetArrayKey(&$array, $keys)
    {
        foreach ($keys as $k => $v) {
            if (isset($array[$v])) {
                unset($array[$v]);
            }
        }
        return $array;
    }

    /**
     * 检测是否开启插件
     * @param string $name
     * @return bool
     */
    public static function checkIsEnablePlugin($name = "area")
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('area');
        } catch (\Exception $ex) {
            \Yii::error('未开启分销插件！！');
            return false;
        }
        return $plugin;
    }


    /**
     * 创建二维码
     * @param $option
     * @param $model
     * @param $path
     * @param $dir
     * @return string
     * @throws \Exception
     */
    public static function createQrcode($option, $model, $path, $dir)
    {
        $mallSettings = AppConfigLogic::getMallSettingConfig(["web_url"]);
        $host = isset($mallSettings["web_url"]) ? urldecode($mallSettings["web_url"]) : "";
        $url = $host . $path;
        $file = \Yii::getAlias('@runtime/image/') . $dir;//生成的二维码保存地址

        if (!file_exists($file)) {
            FileHelper::createDirectory(dirname($file));
            $qrCode = (new QrCode($url, ErrorCorrectionLevelInterface::HIGH))
                ->useEncoding('UTF-8')->setLogoWidth(60)->setSize(300)->setMargin(5);
            $qrCode->writeFile($file);
        }
//        if ($option['qr_code']['type'] == 1) {
//            $file = CommonFunction::avatar($file, $model->temp_path, $option['qr_code']['size'], $option['qr_code']['size']);
//            $file = $model->destroyList($file);
//        }
        //上传到第三方图片存储空间
//        $commonUpload = new CommonUpload();
//        $commonUpload->tempName = $file;
//        $commonUpload->filePath = "/runtime/image/".$dir;
//        $result = $commonUpload->save();
//        \Yii::warning("createQrcode result=".var_export($result,true));
        return $file;
    }

    /**
     * 时间前
     * @param $time
     * @return string
     */
    public static function beforeTime($time)
    {
        $now_time = time();
        $show_time = $time;
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return $time;
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return $time;
                        }
                    }
                }
            }
        }
    }

    /**
     * 返回当前的毫秒时间戳
     */
    public static function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    public static function getMiniWechat($code){
        /** @var Wechat $wechatModel */
        $wechatModel = \Yii::$app->wechat;
        $wechatModel->miniProgram->auth->session($code);
    }

    /**
     * 上图图片到第三方图片存储平台
     * @param $file
     * @param $dir
     * @param $imgUrl
     * @return mixed
     */
    public static function uploadImgToCloudStorage($file,$dir,$imgUrl){
        $storage = AttachmentStorage::findOne(['status' => AttachmentStorage::STATUS_ON]);
        if(!empty($storage)){
            if($storage->type >= 2){
                //保存到第三方图片存储平台
                $commonUpload = new CommonUpload();
                $commonUpload->tempName =  $file;
                $commonUpload->filePath = $dir;
                $result = $commonUpload->save();
                $imgUrl = isset($result["url"]) ? $result["url"] : $imgUrl;
            }
        }
        return $imgUrl;
    }

    /**
     * 触发改变名片客户操作人的任务
     * @param $userId
     * @param $parentId
     * @param $beforeParentId
     * @param $mall_id
     */
    public static function changeCustomerOperator($userId,$parentId,$beforeParentId,$mall_id){
        try{
            \Yii::error("changeCustomerOperator 触发改变名片客户操作人的任务");
            \Yii::$app->queue->delay(0)->push(new \app\plugins\business_card\jobs\ChangeBusinessCardCustomerJob([
                'user_id' => $userId,
                'mall_id' => $mall_id,
                'parent_id' => $parentId,
                'before_parent_id' => $beforeParentId,
            ]));
        }catch (\Exception $ex){
            \Yii::error("changeCustomerOperator 名片插件未开启");
        }
    }
}