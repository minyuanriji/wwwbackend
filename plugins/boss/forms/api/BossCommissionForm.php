<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\boss\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\boss\models\Boss;

class BossCommissionForm extends BaseModel
{

    public $id;
    public $reason;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['reason'], 'string'],
            [['reason'], 'trim'],
        ];
    }


}