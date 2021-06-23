<?php
namespace app\plugins\hotel\libs\bestwehotel;


use app\plugins\hotel\libs\bestwehotel\plateform_action\GetBookingListAction;
use app\plugins\hotel\libs\bestwehotel\plateform_action\ImportAction;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\Hotels;

class PlateForm implements IPlateform{

    public function import($page, $size){
        return (new ImportAction([
            'page' => $page,
            'size' => $size,
            'plateform_class' => get_class($this)
        ]))->run();
    }

    public function getBookingList(Hotels $hotel, HotelPlateforms $hotelPlateform, $startDate, $days){
        return (new GetBookingListAction([
            'hotel'          => $hotel,
            'hotelPlateform' => $hotelPlateform,
            'startDate'      => $startDate,
            'days'           => $days
        ]))->run();
    }
}