<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 16:29
 */

namespace app\forms\mall\wechat;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Material;
use app\models\Wechat;

class MaterialEditForm extends BaseModel
{

    public $name;
    public $media_type;
    public $file_path;
    public $url;
    public $media_id;
    public $material_desc;
    public $id;


    public function rules()
    {
        return [
            [['media_type', 'name'], 'required'],
            [['name'], 'string', 'max' => 45],
            [['material_desc'], 'string', 'max' => 255],
            [['file_path'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $app = \Yii::$app->wechat->getApp();
        $material = Material::findOne(['id' => $this->id, 'is_delete' => 0]);
        if (!$material) {
            $material = new Material();
        }
        switch ($this->media_type) {
            case 'image':
                $result = $app->material->uploadImage(ROOT_PATH . $this->file_path);
                if ($result) {
                    //存入数据库
                    $this->url = $result['url'];
                    $this->media_id = $result['media_id'];
                }
                break;
            case 'video':
                $result = $app->material->uploadVideo(ROOT_PATH . $this->file_path, $this->name, $this->material_desc);
                if ($result) {
                    //存入数据库
                    $this->media_id = $result['media_id'];
                }
                break;
            case 'thumb':
                $result = $app->material->uploadThumb(ROOT_PATH . $this->file_path);
                if ($result) {
                    //存入数据库
                    $this->media_id = $result['media_id'];
                }
                break;
            case 'voice':
                $result = $app->material->uploadVoice(ROOT_PATH . $this->file_path);
                if ($result) {
                    //存入数据库
                    $this->media_id = $result['media_id'];
                }
                break;
        }
        $material->mall_id = \Yii::$app->mall->id;
        $material->name = $this->name;
        $material->wechat_app_id = $app->config->app_id;
        $material->name = $this->name;
        $material->url = $this->url;
        $material->media_type = $this->media_type;
        $material->media_id = $this->media_id;
        $material->material_desc = $this->material_desc;
        if ($material->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '保存失败',
            'error' => $this->responseErrorMsg($material)
        ];

    }


}