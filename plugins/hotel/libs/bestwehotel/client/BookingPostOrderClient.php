<?php
namespace app\plugins\hotel\libs\bestwehotel\client;

use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\request_model\BookingPostOrder;
use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

class BookingPostOrderClient extends BaseClient implements IClient{

    public function parseResponseModel($parseArray)
    {
        // TODO: Implement parseResponseModel() method.
    }
}