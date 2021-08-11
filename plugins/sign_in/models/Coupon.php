<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/8/17
 * Time: 16:14
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;

class Coupon extends BaseActiveRecord
{

    public static function tableName()
    {
        return '{{%coupon}}';
    }

    public function rules()
    {

        return [
            [['mall_id', 'id'], 'required'],
        ];
    }

    public static function getData($params,$fields = []){
        $returnData = [];
        $query = self::find()->where(["is_delete" => self::NO,'is_failure'=>0]);
        if(isset($params["mall_id"]) && !empty($params["mall_id"])){
            $query->andWhere(["mall_id" => $params["mall_id"]]);
        }

        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn." ".$orderByType;
        if(!empty($fields)){
            $query->select($fields);
        }
        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }
        $query->asArray()->orderBy($orderBy);
        if(isset($params["is_one"]) && $params["is_one"] == 1){
            $list = $query->one();
            $returnData = $list;
        }else{
            $list = $query->all();
            if(isset($params["limit"]) && isset($params["page"])) {
                $returnData["list"] = $list;
                $returnData["pagination"] = $pagination;
            }else{
                $returnData = $list;
            }
        }
        return $returnData;
    }


    public function getCouponList($id,$fields=''){
        $query = self::find()->where(["is_delete" => self::NO,'is_failure'=>0]);
        $query->andWhere(['in','id',$id]);

        if(!empty($fields)){
            $query->select($fields);
        }

        $returnData = $query->asArray()->all();
        return $returnData;
    }
}