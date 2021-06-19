<?php
namespace app\plugins\hotel\libs\bestwehotel;


use app\plugins\hotel\libs\bestwehotel\plateform_action\ImportAction;
use app\plugins\hotel\libs\IPlateform;

class PlateForm implements IPlateform{

    public function import($page, $size){
        return (new ImportAction([
            'page' => $page,
            'size' => $size,
            'plateform_class' => get_class($this)
        ]))->run();
    }

}