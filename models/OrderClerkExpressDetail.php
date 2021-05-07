<?php


namespace app\models;


class OrderClerkExpressDetail extends BaseActiveRecord{

    public static function tableName(){
        return '{{%order_clerk_express_detail}}';
    }

    public function rules(){
        return [
            [['mall_id', 'send_type', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'deleted_at'], 'integer'],
            [['express', 'express_no', 'express_content', 'express_code'], 'string']
        ];
    }
}