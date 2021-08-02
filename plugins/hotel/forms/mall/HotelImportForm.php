<?php
namespace app\plugins\hotel\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\libs\ImportResult;
use app\plugins\hotel\libs\IPlateform;

class HotelImportForm extends BaseModel{

    public $page;
    public $size;
    public $plateform_class;

    public function rules(){
        return [
            [['page', 'size', 'plateform_class'], 'required'],
            [['page', 'size'], 'integer', 'min' => 1]
        ];
    }

    public function import(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(!class_exists($this->plateform_class)){
                throw new \Exception("平台类".$this->plateform_class."不存在");
            }

            $class = new $this->plateform_class();
            if(!$class instanceof IPlateform){
                throw new \Exception("平台类必须实现[IPlateform]接口");
            }

            $result = $class->import($this->page, $this->size);
            if(!$result instanceof ImportResult){
                throw new \Exception("导入结果数据类型不正确");
            }

            if($result->code != ImportResult::IMPORT_SUCC){
                throw new \Exception($result->message);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'next_page'   => $result->finished ? 0 : ($this->page + 1),
                    'total_count' => $result->totalCount,
                    'total_pages' => $result->totalPages
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

    }

}