<?php

namespace app\plugins\mch\controllers\api\mana\filter;

use app\core\ApiCode;
use yii\base\ActionFilter;

class LoginFilter extends ActionFilter
{
    public $ignore;
    public $only;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action){
        return true;
    }
}
