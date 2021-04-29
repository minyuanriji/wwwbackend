<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商城设置服务类
 * Author: xuyaoxiang
 * Date: 2020/10/12
 * Time: 9:34
 */

namespace app\services\MallSetting;

use app\models\MallSetting;
use app\services\ReturnData;

class MallSettingService
{
    use ReturnData;

    private $mall_id;

    public function __construct($mall_id)
    {
        $this->mall_id = $mall_id;
    }

    /**
     * 获取设置数据
     * @param $key
     * @param false $json 是否返回json格式数据
     * @return array
     */
    public function getValueByKeyApiData($key)
    {
        return $this->returnApiResultData(0,"成功",$this->getValueByKey($key));
    }

    /**
     * 获取设置数据,code,msg,data
     * @param $key
     * @return array|bool|mixed|string|void code,msg,data
     */
    public function getValueByKey($key){
        $MallSetting = new MallSetting();
        $data        = $MallSetting->getValueByKey($key, $this->mall_id);

        if (false === $data) {
            return [];
        }

        if (JsonService::is_json($data)) {
            return json_decode($data, true);
        }

        return $data;
    }

    /**
     * 新增或更新数据
     * @param $key
     * @param $value
     * @return array
     */
    public function store($key, $value, $name = null, $setting_desc = null)
    {
        $MallSetting = new MallSetting();
        $one         = $MallSetting->getOneBykey($key, $this->mall_id);
        $msg         = "更新成功";

        //不存在的话,便新增
        if (!$one) {
            $one          = new MallSetting();
            $msg = "添加成功";
        }

        $one->mall_id = $this->mall_id;

        $one->key     = $key;

        if($name){
            $one->name =$name;
        }

        if($setting_desc){
            $one->setting_desc =$setting_desc;
        }

        //如是数组,转为json写入;
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $one->value = $value;

        if (!$one->save()) {
            return $this->returnApiResultData(98, $this->responseErrorMsg($one));
        }

        return $this->returnApiResultData(0, $msg, $one);
    }
//    测试数据
//        $MallSettingService = new MallSettingService(5);
//        $value              = [
//            'is_open'             => true,
//            'is_miniapp_priority' => true
//        ];
//        return $MallSettingService->store('wechat_notice', $value);
}