<?php
namespace app\plugins\hotel\libs;


interface IPlateform
{
    /**
     * 导入第三方数据到平台
     * @param $page
     * @param $size
     * @throws HotelException
     * @return ImportResult
     */
    public function import($page, $size);
}