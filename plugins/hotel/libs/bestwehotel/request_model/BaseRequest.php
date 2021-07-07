<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model;


use yii\base\Model;

abstract class BaseRequest extends Model {

    private $jsonString;
    private $error;

    public function build(){

        if(!$this->validate()){
            $this->error = json_encode($this->getErrors());
            return false;
        }

        $dataArray = [];
        $attributes = $this->getAttributes();
        foreach($attributes as $attribute => $value){

            $method = "build" . ucfirst($attribute);
            if(method_exists($this, $method)){
                $value = $this->$method($value);
            }

            if(is_array($value)){
                $dataArray[$attribute] = $value;
            }else{
                $value = trim($value);
                if(strlen($value) > 0){
                    $dataArray[$attribute] = $value;
                }
            }
        }

        $this->jsonString = @json_encode($dataArray);

        return true;
    }

    /**
     * @return string
     */
    public function getJsonString(){
        return $this->jsonString;
    }


    /**
     * @return string
     */
    public function getError(){
        return $this->error;
    }

    abstract public function getUri();
}