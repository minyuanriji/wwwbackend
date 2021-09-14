<?php
namespace app\forms\api;


interface ICacheForm
{
    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm();

    /**
     * @return array
     */
    public function getCacheKey();
}
