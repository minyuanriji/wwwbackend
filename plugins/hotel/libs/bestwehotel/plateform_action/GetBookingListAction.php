<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\helpers\ArrayHelper;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelRoomStatusClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelRoomStatusRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\plateform\BookingListItemModel;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\models\HotelPics;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\HotelRoom;
use yii\base\BaseObject;

class GetBookingListAction extends BaseObject {

    public $hotel;
    public $hotelPlateform;
    public $startDate;
    public $days;

    public function run(){
        $bookingListResult = new BookingListResult();

        try {
            $requestModel = new GetHotelRoomStatusRequest([
                "innId"    => $this->hotelPlateform->plateform_code,
                "endOfDay" => $this->startDate,
                "days"     => $this->days
            ]);
            $client = new GetHotelRoomStatusClient($requestModel);
            $result = Request::execute($client);
            if(!$result instanceof HotelResponse){
                throw new HotelException("返回结果对象异常");
            }

            if($result->code != HotelResponse::CODE_SUCC){
                throw new HotelException($result->error);
            }

            list($rooms, $rows, $pics) = $this->prepareData($result);
            foreach($result->responseModel->roomTypeList as $item){
                if(!$item->productList || !isset($rows[$item->roomTypeCode]))
                    continue;
                $productCode = $rows[$item->roomTypeCode];
                $room = $rooms[$productCode];
                $bookingItem = new BookingListItemModel();
                $bookingItem->hotel_plateform_id = $this->hotelPlateform->id;
                $bookingItem->product_thumb = isset($pics[$productCode]) ? $pics[$productCode] : "";
                $bookingItem->product_code  = $rows[$item->roomTypeCode];
                $bookingItem->product_name  = $item->roomTypeName;
                foreach($item->productList as $product){
                    $uniqueId = $this->hotel->id . ":" . $this->hotelPlateform->id . ":" . $product->productCode;
                    $bookingItem->unique_id     = $uniqueId;
                    $bookingItem->product_num   = $product->quota;
                    $bookingItem->product_price = $product->advanceRate;
                    $bookingItem->is_breakfast  = $product->advanceBreakfastCount ? 1 : 0;
                    $bookingItem->ban_smoking   = $room['policy_ban_smoking'] ? 1 : 0;
                    $bookingItem->window        = $room['window'];
                    $bookingItem->bed_type      = $room['bed_type'];
                    $bookingItem->origin_data   = ArrayHelper::toArray($product);
                    $bookingListResult->addItem($bookingItem);
                }
            }

            $bookingListResult->code = BookingListResult::CODE_SUCC;
        }catch (HotelException $e){
            $bookingListResult->code = BookingListResult::CODE_FAIL;
            $bookingListResult->message = $e->getMessage() . ";file=" . $e->getFile() . ";line=" . $e->getLine();
        }

        return $bookingListResult;
    }

    private function prepareData(HotelResponse $result){
        $selects = ["product_code", "bed_type", "name", "bed_width",
            "floor", "max_room", "room_size", "window", "people_num",
            "policy_ban_smoking", "policy_add_bed", "policy_breakfast"];

        $rooms = HotelRoom::find()->where([
            "hotel_id" => $this->hotel->id,
            "is_delete" => 0
        ])->select($selects)->asArray()->all();
        $productCodes = [];
        $roomTmps = $rooms;
        if($roomTmps){
            $rooms = [];
            foreach($roomTmps as $room){
                $productCodes[] = $room['product_code'];
                $rooms[$room['product_code']] = $room;
            }
        }

        $pics = HotelPics::find()->andWhere([
            "AND",
            ["hotel_id" => $this->hotel->id],
            ["IN", "room_product_code", $productCodes]
        ])->asArray()->select(["room_product_code", "pic_url"])->all();
        if($pics){
            $tmpPics = $pics;
            $pics = [];
            foreach($tmpPics as $pic){
                $pics[$pic['room_product_code']] = $pic['pic_url'];
            }
        }

        $roomTypeCodes = [];
        foreach($result->responseModel->roomTypeList as $item){
            $roomTypeCodes[] = $item->roomTypeCode;
        }

        $rows = HotelPlateforms::find()->select(["source_code", "plateform_code"])->andWhere([
            "AND",
            ["type" => "room"],
            ["IN", "source_code", $productCodes],
            ["IN", "plateform_code", $roomTypeCodes]
        ])->asArray()->all();
        if($rows){
            $tmpRows = $rows;
            $rows = [];
            foreach($tmpRows as $row){
                $rows[$row['plateform_code']] = $row['source_code'];
            }
        }

        return [$rooms, $rows, $pics];
    }
}