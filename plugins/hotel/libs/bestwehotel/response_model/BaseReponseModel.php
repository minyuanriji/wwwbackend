<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model;

use yii\base\Model;

/***
 * Class BaseReponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model
 * @property int $msgCode
 * @property string $message
 */
abstract class BaseReponseModel extends Model{

    const MSG_CODE_SUCC = 0;

    public $msgCode;
    public $message;

    public static function create($data){
        $responseModel = new static();
        $keys = array_keys($responseModel->getAttributes());
        foreach($keys as $key){
            if(isset($data[$key])){
                $method = "set" . ucfirst($key);
                if(method_exists($responseModel, $method)){
                    $responseModel->$method($data[$key]);
                }else{
                    $responseModel->$key = $data[$key];
                }
            }
        }
        return $responseModel;
    }
}