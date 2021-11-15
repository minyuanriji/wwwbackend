<?php

namespace app\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;

class OrderExtendedTimeForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'integer'],
        ];
    }

    /**
     * 延长收货时间
     * @return array
     * @throws \Exception
     */
    public function extended()
    {
        if (!$this->validate()) {
            $this->responseErrorInfo();
        }

        try {
            $orderResult = Order::findOne($this->id);
            if (!$orderResult || $orderResult->is_delete || $orderResult->is_recycle)
                throw new \Exception('订单不存在');

            if ($orderResult->status != Order::STATUS_WAIT_RECEIVE)
                throw new \Exception('当前订单状态不可延长操作');

            if ($orderResult->expand_num >= 1)
                throw new \Exception('订单已经延长时间');

            $orderResult->expand_num += 1;
            if (!$orderResult->save())
                throw new \Exception($orderResult->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        } catch (\Exception $ex) {
            return $this->returnApiResultData($ex->getMessage());
        }
    }
}
