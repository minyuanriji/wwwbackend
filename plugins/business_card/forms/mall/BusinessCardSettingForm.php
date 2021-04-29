<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\business_card\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCardSetting;
use yii\helpers\Json;

class BusinessCardSettingForm extends BaseModel
{
    public $company_name;
    public $company_address;
    public $company_logo;
    public $company_img;
    public $card_token;
    public $video_size;
    public $tag_list;
    public $data;

    public function rules()
    {
        return [
            [["company_name","card_token","company_address","company_logo"],"string"],
            [["video_size"],"integer"]
        ];
    }

    public function attributeLabels()
    {
        return [
            'company_name' => '公司名称',
            'company_address' => '公司地址',
            'company_logo' => '公司logo',
            'company_img' => '公司图片',
            'card_token' => '名片命令',
            'video_size' => '视频大小',
        ];
    }


    public static function strToNumber($key, $str)
    {
        $default = [];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $setting_list = [];
            foreach ($this->attributes as $index => $item) {
                $setting_list[] = [
                    'key' => $index,
                    'value' =>$item
                ];
            }

            foreach ($setting_list as $item) {
                $setting = BusinessCardSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new BusinessCardSetting();
                }
                if($item["key"] == BusinessCardSetting::COMPANY_IMG){
                    $item['value'] = SerializeHelper::encode($item["value"]);
                }
                $setting->key = $item['key'];
                $setting->value = $item['value'];
                $setting->mall_id = \Yii::$app->mall->id;
                $setting->is_delete = 0;

                $setting->save();

            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        $list = BusinessCardSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        $newItem = [];
        foreach ($list as $item) {
            if ($item['key'] == BusinessCardSetting::TAG) {
                //$item['value'] = SerializeHelper::decode($item['value'],true);
            }
            if ($item['key'] == BusinessCardSetting::COMPANY_IMG) {
                $item['value'] = SerializeHelper::decode($item['value'],true);
            }else{
                $item['value'] = self::strToNumber($item['key'], $item['value']);
            }
            $newItem[$item['key']] =  $item['value'];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => $newItem
            ]
        ];
    }

    public function addTag(){
        try{
            $setting = BusinessCardSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => BusinessCardSetting::TAG]);
            if (!$setting) {
                $setting = new BusinessCardSetting();
            }
            $setting->key = BusinessCardSetting::TAG;
            $setting->value = SerializeHelper::encode(explode(',',$this->tag_list["tag_list"]));
            $setting->mall_id = \Yii::$app->mall->id;
            $setting->is_delete = 0;

            if (!$setting->save()){
                print_r($setting->getErrors());
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '保存失败'
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $ex){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $ex->getMessage()
            ];
        }
    }

    public function searchTag()
    {
        $item = BusinessCardSetting::find()->where(['mall_id' => \Yii::$app->mall->id,'key' => BusinessCardSetting::TAG, 'is_delete' => 0])->asArray()->one();
        $tagList = isset($item['value']) && !empty($item["value"]) ? json_decode($item['value'],true) : [];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => join(',',$tagList)
            ]
        ];
    }

    /**
     * 保存海报
     * @return array
     */
    public function savePoster()
    {
        try {
            $this->checkData();
            $newData = [];
            foreach ($this->data as $key => $datum) {
                $newData[$key] = (new AppConfigLogic())->saveEnd($datum);
                //$newData[$key] = $datum;
            }
            $setting = BusinessCardSetting::findOne(['mall_id' => \Yii::$app->mall->id,'is_delete' => BusinessCardSetting::IS_DELETE_NO, 'key' => BusinessCardSetting::POSTER]);
            if (empty($setting)) {
                $setting = new BusinessCardSetting();
            }
            $setting->key = BusinessCardSetting::POSTER;
            $setting->value = \Yii::$app->serializer->encode($newData);;
            $setting->mall_id = \Yii::$app->mall->id;
            $setting->is_delete = 0;

            $res = $setting->save();
            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检测数据是否完整
     * @throws \Exception
     */
    public function checkData()
    {
        if (!$this->data && is_array($this->data)) {
            throw new \Exception('请检查信息是否填写完整');
        }
    }

    public function getDetail()
    {
        $poster = BusinessCardSetting::find()->where(['mall_id' => \Yii::$app->mall->id,'key' => BusinessCardSetting::POSTER, 'is_delete' => 0])->asArray()->one();
        $newPoster = [];
        if(!empty($poster)){
            $newPosters = json_decode($poster["value"],true);
            $newPosters = $newPosters["business_card"];
            $newPoster["business_card"] = (new AppConfigLogic())->poster($newPosters);
        }else {
            $newPoster["business_card"] = (new AppConfigLogic())->poster([], Common::getDefault()["business_card"]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newPoster,
            ]
        ];
    }

}