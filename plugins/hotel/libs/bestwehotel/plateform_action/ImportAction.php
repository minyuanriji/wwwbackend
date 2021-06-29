<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;

use app\helpers\ArrayHelper;
use app\helpers\CityHelper;
use app\models\DistrictData;
use app\plugins\hotel\libs\bestwehotel\CityData;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelIdsClient;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelImageClient;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelInfoClient;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelRoomTypeClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelIdsRequest;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelImageRequest;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelInfoRequest;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelRoomTypeRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\ImportResult;
use app\plugins\hotel\models\HotelMap;
use app\plugins\hotel\models\HotelPics;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;
use yii\base\BaseObject;

class ImportAction extends BaseObject {

    public $page;
    public $size;
    public $plateform_class;

    public function run(){
        $result = new ImportResult();

        $trans = \Yii::$app->getDb()->beginTransaction();

        $hotelInfo = null;
        try {
            //获取酒店ID列表
            $requestModel = new GetHotelIdsRequest([
                "pageNum"  => $this->page,
                "pageSize" => $this->size
            ]);
            $client = new GetHotelIdsClient($requestModel);

            $response = Request::execute($client);

            if($response->code != HotelResponse::CODE_SUCC){
                throw new HotelException($response->error);
            }

            $result->totalCount = $response->responseModel->total;
            $result->totalPages = $response->responseModel->pages;
            $items = $response->responseModel->list;
            foreach($items as $item){

                //获取酒店信息
                $requestModel = new GetHotelInfoRequest([
                    "innId"  => $item->innId
                ]);
                $client = new GetHotelInfoClient($requestModel);
                $response = Request::execute($client);
                if($response->code != HotelResponse::CODE_SUCC){
                    throw new HotelException($response->error);
                }

                $hotelInfo = ArrayHelper::toArray($response->responseModel);
                $hotelInfo['mapInfo'] = [];
                foreach($response->responseModel->getMapInfo() as $mapInfo){
                    $hotelInfo['mapInfo'][] = ArrayHelper::toArray($mapInfo);
                }

                if($hotelInfo['status'] != 1 || $hotelInfo['bookFlag'] != 1)
                    continue;

                //获取房间信息
                $requestModel = new GetHotelRoomTypeRequest([
                    "innId" => $item->innId
                ]);
                $client = new GetHotelRoomTypeClient($requestModel);
                $response = Request::execute($client);
                if($response->code != HotelResponse::CODE_SUCC){
                    throw new HotelException($response->error);
                }
                $hotelInfo['rooms'] = [];
                foreach($response->responseModel->datas as $data){
                    $hotelInfo['rooms'][] = ArrayHelper::toArray($data);
                }

                //获取图片
                $requestModel = new GetHotelImageRequest([
                    "innId" => $item->innId
                ]);
                $client = new GetHotelImageClient($requestModel);
                $response = Request::execute($client);
                if($response->code != HotelResponse::CODE_SUCC){
                    throw new HotelException($response->error);
                }
                $hotelInfo['images'] = [];
                foreach($response->responseModel->datas as $data){
                    $hotelInfo['images'][] = ArrayHelper::toArray($data);
                }

                $this->save($hotelInfo);
            }

            $trans->commit();

            $result->code = ImportResult::IMPORT_SUCC;

            if($this->page >= $result->totalPages){
                $result->finished = 1;
            }
        }catch (HotelException $e){
            $trans->rollBack();
            $result->code = ImportResult::IMPORT_FAIL;
            $result->message = $e->getMessage() . ";file=" . $e->getFile() . ";line=" . $e->getLine();
        }

        return $result;
    }

    /**
     * 保存信息
     * @param $hotelInfo
     */
    private function save($info){
        $plateform = HotelPlateforms::findOne([
            'type'            => 'hotel',
            'plateform_code'  => $info['innId'],
            'plateform_class' => $this->plateform_class,
            'mall_id'         => \Yii::$app->mall->id
        ]);
        if($plateform){
            $hotel = Hotels::findOne($plateform->source_code);
        }else{
            $plateform = new HotelPlateforms([
                'type'            => 'hotel',
                'plateform_code'  => $info['innId'],
                'plateform_class' => $this->plateform_class,
                'mall_id'         => \Yii::$app->mall->id
            ]);
            $hotel = null;
        }

        if(!$hotel){
            $hotel = new Hotels([
                'mall_id'    => \Yii::$app->mall->id,
                'thumb_url'  => "",
                'name'       => $info['innName'],
                'type'       => 'eco',
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }

        //保存酒店信息
        $this->saveHotel($plateform, $hotel, $info);

        //保存地图坐标
        $this->saveMaps($hotel, $info);

        //保存房型信息
        $this->saveRooms($hotel, $info);

        //保存图片信息
        $this->saveImages($hotel, $info);
    }

    /**
     * 保存酒店信息
     * @param Hotels $hotel
     * @param $info
     */
    private function saveHotel(HotelPlateforms $plateform, Hotels $hotel, $info){
        if(!empty($info['openDate'])){ //开业时间、装修时间
            $hotel->open_time = date("Y", $info['openDate']/1000);
            $hotel->building_time = $hotel->open_time;
        }

        $hotel->descript      = $info['description']; //描述信息
        $hotel->contact_phone = $info['innPhone']; //酒店电话
        $hotel->address       = $info['address']; //酒店地址

        $this->setCity($hotel, $info['cityCode']); //设置城市

        $this->setHotelType($hotel, $info['innType']); //设置酒店类型

        $this->setHotelThumb($hotel, $info['images']); //设置缩略图

        if(!$hotel->save()){
            throw new HotelException(json_encode($hotel->getErrors()));
        }

        $plateform->source_code = $hotel->id;
        $plateform->plateform_json_data = json_encode($info);
        if(!$plateform->save()){
            throw new HotelException(json_encode($plateform->getErrors()));
        }
    }

    /**
     * 保存地图坐标
     * @param Hotels $hotel
     * @param $info
     */
    private function saveMaps(Hotels $hotel, $info){

        /**
         * 百度地图BD09坐标---->中国正常GCJ02坐标
         * 腾讯地图用的也是GCJ02坐标
         * @param double $lat 纬度
         * @param double $lng 经度
         * @return array();
         */
        $Convert_BD09_To_GCJ02 = function($lat, $lng){
            $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
            $x = $lng - 0.0065;
            $y = $lat - 0.006;
            $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
            $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
            $lng = $z * cos($theta);
            $lat = $z * sin($theta);
            return ['lng'=>$lng, 'lat'=>$lat];
        };

        HotelMap::deleteAll([
            "hotel_id" => $hotel->id
        ]);
        if($info['mapInfo']){
            foreach($info['mapInfo'] as $mapInfo){
                $map = new HotelMap([
                    "mall_id"   => \Yii::$app->mall->id,
                    "hotel_id"  => $hotel->id,
                    "longitude" => $mapInfo['lng'],
                    "latitude"  => $mapInfo['lag']
                ]);
                if($mapInfo['mapType'] == 0){
                    $map->type = "bd";
                    if(empty($hotel->tx_lat) || empty($hotel->tx_lng)){
                        $gcj02 = $Convert_BD09_To_GCJ02($mapInfo['lag'], $mapInfo['lng']);
                        $hotel->tx_lat = $gcj02['lat'];
                        $hotel->tx_lng = $gcj02['lng'];
                    }
                }elseif($mapInfo['mapType'] == 2){
                    $map->type = "tx";
                    if(empty($hotel->tx_lat) || empty($hotel->tx_lng)){
                        $hotel->tx_lat = $mapInfo['lag'];
                        $hotel->tx_lng = $mapInfo['lng'];
                    }
                }elseif($mapInfo['mapType'] == 3){
                    $map->type = "gd";
                    if(empty($hotel->tx_lat) || empty($hotel->tx_lng)){
                        $hotel->tx_lat = $mapInfo['lag'];
                        $hotel->tx_lng = $mapInfo['lng'];
                    }
                }else{
                    $map->type = "un";
                    if(empty($hotel->tx_lat) || empty($hotel->tx_lng)){
                        $hotel->tx_lat = $mapInfo['lag'];
                        $hotel->tx_lng = $mapInfo['lng'];
                    }
                }
                if(!$map->save()){
                    throw new HotelException(json_encode($map->getErrors()));
                }
            }
        }

        if(!$hotel->save()){
            throw new HotelException(json_encode($hotel->getErrors()));
        }
    }

    /**
     * 保存房型信息
     * @param Hotels $hotel
     * @param $info
     */
    private function saveRooms(Hotels $hotel, $info){

        foreach($info['rooms'] as $roomType){

            $plateform = HotelPlateforms::findOne([
                'type'            => 'room',
                'mall_id'         => $hotel->mall_id,
                'plateform_code'  => $roomType['sCode'],
                'plateform_class' => $this->plateform_class
            ]);
            $room = $hotel->getRoomByPlateform($plateform);
            if(!$room){

                $productCode = date("ymdhis") . rand(100, 999);
                while(HotelRoom::findOne(["product_code" => $productCode])){
                    $productCode = date("ymdhis") . rand(100, 999);
                }

                $plateform = new HotelPlateforms([
                    'mall_id'         => \Yii::$app->mall->id,
                    'type'            => 'room',
                    'source_code'     => $productCode,
                    'plateform_code'  => $roomType['sCode'],
                    'plateform_class' => $this->plateform_class
                ]);

                $room = new HotelRoom([
                    'mall_id'      => \Yii::$app->mall->id,
                    'hotel_id'     => $hotel->id,
                    'product_code' => $productCode,
                    'bed_type'     => 'single',
                    'created_at'   => time(),
                    'updated_at'   => time()
                ]);
            }

            $plateform->plateform_json_data = json_encode($roomType);
            if(!$plateform->save()){
                throw new HotelException(json_encode($plateform->getErrors()));
            }


            $this->saveRoom($room, $roomType);
        }
    }

    /**
     * 保存房型信息
     * @param HotelRoom $room
     * @param $roomType
     * @throws HotelException
     */
    private function saveRoom(HotelRoom $room, $roomType){
        $widthSet = [
            "14" => 2.6,
            "13" => 2.4,
            "12" => 2.3,
            "11" => 2.2,
            "10" => 2,
            "9"  => 1.8,
            "8"  => 1.65,
            "7"  => 1.6,
            "6"  => 1.5,
            "5"  => 1.4,
            "4"  => 1.35,
            "3"  => 1.3,
            "2"  => 1.2,
            "1"  => 1.1,
            "0"  => 1
        ];

        $room->bed_width = isset($widthSet[$roomType['bedWidth']]) ? $widthSet[$roomType['bedWidth']] : 0; //床宽
        $room->floor     = $roomType['floor']; //楼层

        //床型
        $bedTypeSet = ["1" => "double", "2" => "single", "0" => "big", "101" => "other"];
        $room->bed_type = isset($bedTypeSet[$roomType['bedType']]) ? $bedTypeSet[$roomType['bedType']] : "other";

        //窗户
        $windowSet = ['0' => 'no', '1' => 'out', '2' => 'part_no', '3' => 'inner', '4' => 'part_inner'];
        if(isset($windowSet[$roomType['window']])){
            $room->window = $windowSet[$roomType['window']];
        }

        $room->policy_add_bed = $roomType['addBed'] ? 1 : 0; //加床
        $room->people_num     = (int)$roomType['maxCheckIn']; //人数
        $room->max_room       = (int)$roomType['maxRoom']; //最大房间数量
        $room->name           = $roomType['roomTypeName'];

        if(!$room->save()){
            throw new HotelException(json_encode($room->getErrors()));
        }

    }


    private function saveImages(Hotels $hotel, $info){
        if($info['images']){
            foreach($info['images'] as $image){
                $pic = HotelPics::findOne([
                    "mall_id"  => \Yii::$app->mall->id,
                    "hotel_id" => $hotel->id,
                    "pic_url"  => $image['imageUrl']
                ]);
                if(!$pic){
                    $pic = new HotelPics([
                        "mall_id"    => \Yii::$app->mall->id,
                        "hotel_id"   => $hotel->id,
                        "pic_url"    => $image['imageUrl'],
                        "created_at" => time()
                    ]);
                }
                $pic->descript = $image['imageDes'];

                $plateform = HotelPlateforms::findOne([
                    "mall_id"         => \Yii::$app->mall->id,
                    "type"            => "room",
                    "plateform_code"  => $image['roomTypeCode'],
                    "plateform_class" => $this->plateform_class
                ]);
                if($plateform){
                    $pic->room_product_code = $plateform->source_code;
                }

                if(!$pic->save()){
                    throw new HotelException(json_encode($pic->getErrors()));
                }
            }
        }
    }


    /**
     * 设置酒店类型
     * @param Hotels $hotel
     * @param $innType 酒店类别(100经济型酒店 101 精品商务酒店102 景区度假酒店 103 主题特色酒店 104 民族风情酒店)
     */
    private function setHotelType(Hotels $hotel, $innType){

        if(in_array($innType, [103, 104])){ //豪华型
            $hotel->type = "luxe";
        }elseif(in_array($innType, [102, 102])){
            $hotel->type = "comfort";
        }else{
            $hotel->type = "eco";
        }
    }

    /**
     * 设置城市
     * @param Hotels $hotel
     * @param $cityCode
     */
    private function setCity(Hotels $hotel, $code){
        $citys = CityData::arr();
        if(empty($code) || !$citys[$code])
            return;

        $cityInfo = null;
        switch ($citys[$code]['type']){
            case 1: //省份/洲
                $cityInfo = CityHelper::likeSearch($citys[$code]['name']);
                break;
            case 2: //直辖市
                $name = $citys[$code]['name'];
                $cityInfo = CityHelper::likeSearch($name, $name);
                break;
            case 3: //城市
                $parentCode = $citys[$code]['parent_code'];
                if(!empty($parentCode) && isset($citys[$parentCode])){
                    $cityInfo = CityHelper::likeSearch($citys[$parentCode]['name'],
                                    $citys[$code]['name']);
                }
                break;
            case 4: //行政区
            case 5: //县级市
                $parentCode1 = $citys[$code]['parent_code'];
                if(!empty($parentCode1) && isset($citys[$parentCode1])){
                    $parent1Data = $citys[$parentCode1];
                    if($parent1Data['type'] == 2){
                        $cityInfo = CityHelper::likeSearch($parent1Data['name'],
                                        $parent1Data['name'], $citys[$code]['name']);
                    }else if($parent1Data['type'] == 3){
                        $parentCode2 = $parent1Data['parent_code'];
                        if(!empty($parentCode2) && isset($citys[$parentCode2])){
                            $parent2Data = $citys[$parentCode2];
                            $cityInfo = CityHelper::likeSearch($parent2Data['name'],
                                            $parent1Data['name'], $citys[$code]['name']);
                        }
                    }
                }
                break;
        }

        if(!empty($cityInfo)){
            $hotel->province_id = $cityInfo['province_id'];
            $hotel->city_id     = $cityInfo['city_id'];
            $hotel->district_id = $cityInfo['district_id'];
        }
    }

    /**
     * 把图片做下分组
     * @param $images
     */
    private function setHotelThumb($hotel, $images){
        $groupMasters1 = $groupMasters2 = $groupImages1 = $groupImages2 = [];
        foreach($images as $image){
            $sizeType = isset($image['sizeType']) ? (int)$image['sizeType'] : 0;
            if($image['master']){
                if($sizeType == 2){
                    $groupMasters1[$image['imageType']][] = $image;
                }else{
                    $groupMasters2[$image['imageType']][] = $image;
                }
            }else{
                if($sizeType == 2){
                    $groupImages1[$image['imageType']][] = $image;
                }else{
                    $groupImages2[$image['imageType']][] = $image;
                }
            }
        }

        $thumb = $this->pickAsThumb($groupMasters1);
        if(empty($thumb)){
            $thumb = $this->pickAsThumb($groupMasters2);
        }
        if(empty($thumb)){
            $thumb = $this->pickAsThumb($groupImages1);
        }
        if(empty($thumb)){
            $thumb = $this->pickAsThumb($groupImages2);
        }

        $hotel->thumb_url = $thumb;
    }

    /**
     * 取出一张图片作为封面
     * @param $images
     * @return string
     */
    private function pickAsThumb($groupImages){
        ksort($groupImages);
        $thumb = "";
        foreach($groupImages as $images){
            foreach($images as $image){
                $thumb = $image['imageUrl'];
                break;
            }
        }
        return $thumb;
    }
}