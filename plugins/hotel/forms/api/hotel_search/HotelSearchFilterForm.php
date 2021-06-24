<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\core\ApiCode;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\models\Hotels;

class HotelSearchFilterForm extends HotelSearchForm{

    public $prepare_id;

    public function rules(){
        return [
            [['prepare_id'], 'required']
        ];
    }

    public function filter(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $filterResult = ['prepare_id' => $this->prepare_id, 'finished' => 1, 'founds' => 0];

            $data = $this->popPrepareData($this->prepare_id);
            $hotelIds = isset($data['hotel_ids']) ? $data['hotel_ids'] : [];
            $attrs    = isset($data['attrs']) ? $data['attrs'] : [];
            $searchId = isset($data['search_id']) ? $data['search_id'] : "";

            if(empty($searchId)){
                throw new \Exception("搜索异常");
            }

            $filterResult['search_id'] = $searchId;

            $hotels = Hotels::find()->andWhere([
                "AND",
                ["is_open" => 1],
                ["is_booking" => 1],
                ["is_delete" => 0],
                ["IN", "id", $hotelIds ? $hotelIds : []]
            ])->all();

            if(!empty($hotels)){
                $filterResult['finished'] = 0;
                $founds = [];
                foreach($hotels as $hotel){
                    $hotelPlateforms = $hotel->getPlateforms();
                    foreach($hotelPlateforms as $hotelPlateform){
                        $className = $hotelPlateform->plateform_class;
                        if(empty($className) || !class_exists($className)) continue;
                        $classObject = new $className();
                        if(!$classObject instanceof IPlateform) continue;
                        $result = $classObject->getBookingList($hotel, $hotelPlateform, $attrs['start_date'], $attrs['days']);
                        if(!$result instanceof BookingListResult)
                            continue;
                        if($result->code != BookingListResult::CODE_SUCC)
                            continue;
                        $bookings = $result->getAll();
                        if($bookings && count($bookings) > 0){
                            $founds[] = $hotel->id;
                            break;
                        }
                    }
                }

                $this->pushFound($searchId, $this->prepare_id, $founds);

                $filterResult['founds'] = count($founds);
            }

            if($filterResult['finished']){
                $this->updateFound($searchId, $this->prepare_id);
            }else{
                $this->addSearchTask($searchId, $this->prepare_id);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $filterResult
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}