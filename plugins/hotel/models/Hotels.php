<?php
namespace app\plugins\hotel\models;

use app\models\BaseActiveRecord;

class Hotels extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_hotels}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'thumb_url', 'name', 'type', 'created_at', 'updated_at'], 'required'],
            [['tx_lat', 'tx_lng', 'is_delete', 'is_open', 'is_booking', 'open_time', 'building_time', 'descript', 'price',
              'tag', 'cmt_grade', 'cmt_text1', 'cmt_text2', 'cmt_num', 'contact_phone',
              'contact_mobile', 'address', 'near_subway', 'policy_into_time',
              'policy_out_time', 'policy_add_bed', 'policy_pets', 'policy_breakfast',
              'json_service_facilitys', 'province_id', 'city_id', 'district_id',
              'recommander_uid', 'transfer_rate'], 'safe']
        ];
    }

    /**
     * 获取房型信息
     * @param $plateform
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getRoomByPlateform($plateform){
        $room = null;
        if($plateform && $plateform->type == "room"){
            $room = HotelRoom::find()->where([
                "hotel_id"     => $this->id,
                "product_code" => $plateform->source_code
            ])->one();
        }
        return $room;
    }

    /**
     * 获取合作平台
     * @param $plateform_class
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getPlateform($plateform_class){
        return HotelPlateforms::find()->where([
            'type'            => 'hotel',
            'source_code'     => $this->id,
            'plateform_class' => $plateform_class,
            'mall_id'         => $this->mall_id
        ])->one();
    }

    /**
     * 获取所有合作平台
     * @return HotelPlateforms[]
     */
    public function getPlateforms(){
        return HotelPlateforms::find()->where([
            'type'        => 'hotel',
            'source_code' => $this->id,
            'mall_id'     => $this->mall_id
        ])->all();
    }
}