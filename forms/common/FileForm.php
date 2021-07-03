<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 11:48
 */

namespace app\forms\common;

use app\core\ApiCode;
use app\models\BaseModel;

class FileForm extends BaseModel
{
    public $file;

    public function save()
    {
        try {
            if ($this->file->extension !== 'txt') {
                throw new \Exception('文件格式不正确, 请上传 .txt 格式文件');
            }

            if ($this->file->size !== 32) {
                throw new \Exception('文件内容长度不正确, 请检查');
            }

            $saveFile = \Yii::$app->basePath . '/' . $this->file->name;
            if ($this->file->saveAs($saveFile)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '上传成功',
                ];
            } else {
                throw new \Exception('文件保存失败，请检查目录写入权限。');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 上传logo文件
     * @return array
     */
    public function saveLogo()
    {
        try {
            if ($this->file->extension !== 'ico') {
                throw new \Exception('文件格式不正确, 请上传 .ico 格式文件');
            }

            $saveFile = \Yii::$app->basePath . '/' . 'favicon.ico';
            if ($this->file->saveAs($saveFile)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '上传成功',
                ];
            } else {
                throw new \Exception('文件保存失败，请检查目录写入权限。');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
