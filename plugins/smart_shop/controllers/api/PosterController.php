<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\forms\api\PosterPosterForm;

class PosterController extends ApiController {

    public function actionPoster(){
        $form = new PosterPosterForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

}