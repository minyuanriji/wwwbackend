<?php

namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\helpers\PinyinHelper;
use app\models\BaseModel;
use app\models\DistrictData;

class CityListForm extends BaseModel
{
    public function getList()
    {
        $key = 'Hotel_City_List11';
        $cache = \Yii::$app->getCache();
        $resultArray = $cache->get($key);
        if (!$resultArray) {
            $PinyinHelper = new PinyinHelper();
            $arr = DistrictData::getArr();
            foreach ($arr as $key => $item) {
                if ($item['level'] == 'city') {
                    $resultArray[$key]['cityName'] = $item['name'];
                    $resultArray[$key]['id'] = $item['id'];
                    $resultArray[$key]['parent_id'] = $item['parent_id'];
                    $resultArray[$key]['level'] = $item['level'];
                    $cityNameLen = mb_strlen($item['name'], 'Utf-8');
                    for ($i = 0; $i < $cityNameLen; $i++) {
                        $resultArray[$key]['py'] .= $PinyinHelper->getStrInitial(mb_substr($item['name'], $i, 4, 'UTF-8'));
                    }
                }
            }
//            $resultArray = $this->data_letter_sort(array_values($resultArray), 'py');
            $cache->set($key, $resultArray);
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', array_values($resultArray));
    }


    public function data_letter_sort($list, $field) {
        $resault = array();

        foreach( $list as $key => $val ){
            // 添加 # 分组，用来 存放 首字母不能 转为 大写英文的 数据
            $resault['#'] = array();
            // 首字母 转 大写英文
            $letter = strtoupper( substr($val[$field], 0, 1) );
            // 是否 大写 英文 字母
            if( !preg_match('/^[A-Z]+$/', $letter) ){
                $letter = '#';
            }
            // 创建 字母 分组
            if( !array_key_exists($letter, $resault) ){
                $resault[$letter] = array();
            }
            // 字母 分组 添加 数据
            Array_push($resault[$letter], $val);
        }
        // 依据 键名 字母 排序，该函数 返回 boolean
        ksort($resault);
        // 将 # 分组 放到 最后
        $arr_last = $resault['#'];
        unset($resault['#']);
        $resault['#'] = $arr_last;

        return $resault;
    }
}