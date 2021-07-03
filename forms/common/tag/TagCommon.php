<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单公共处理类
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:16
 */

namespace app\forms\common\tag;


use app\core\ApiCode;
use app\models\BaseModel;
use app\forms\OrderConfig;
use app\models\ObjectTag;

class TagCommon extends BaseModel
{
    /**
     * 新增对象标签数据
     * @param $params
     * @return bool
     */
    public static function addObjectTag($params)
    {
        $selectParams = $params;
        $selectParams["return_count"] = 1;
        $isExist = ObjectTag::getData($selectParams);
        if(!empty($isExist)){
            \Yii::warning("TagCommon addObjectTag already add params=".var_export($params,true));
            return true;
        }
        $result = ObjectTag::operateData($params);
        return $result;
    }

    /**
     * 获取对象标签
     * @param $objectId
     * @param $catId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public static function getObjectTag($objectId,$catId,$limit = 8,$page = 1)
    {
        $returnData = $data = [];
        $params = [];
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["cat_id"] = $catId;
        $params["object_id"] = $objectId;
        $params["tag"] = 1;
        $params["limit"] = 8;
        $params["page"] = 1;
        $list = ObjectTag::getData($params,["id","object_id","tag_id"]);
        if(!empty($list)){
            foreach ($list["list"] as $k => $v) {
                $data["name"] = isset($v["tag"]) ? $v["tag"]["name"] : "无标签";
                $returnData[] = $data;
            }
        }
        return $returnData;
    }
}