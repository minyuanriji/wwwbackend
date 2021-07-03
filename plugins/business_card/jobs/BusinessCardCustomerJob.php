<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片客户任务类
 * Author: zal
 * Date: 2020-07-23
 * Time: 18:35
 */

namespace app\plugins\business_card\jobs;

use app\models\Mall;
use app\plugins\business_card\models\BusinessCardCustomer;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BusinessCardCustomerJob extends Component implements JobInterface
{
    public $user_id;
    public $parent_id;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("------BusinessCardCustomerJob start---------");
        \Yii::warning("BusinessCardCustomerJob user_id={$this->user_id},parent_id={$this->parent_id},mall_id={$this->mall_id}");
        \Yii::$app->setMall(Mall::findOne($this->mall_id));
        $params = [];
        $params["user_id"] = $this->user_id;
        $params["operate_id"] = $this->parent_id;
        $params["return_count"] = 1;
        $businessCardCustomer = BusinessCardCustomer::getData($params);
        if(empty($businessCardCustomer)){
            $data = [];
            $data["mall_id"] = $this->mall_id;
            $data["user_id"] = $this->user_id;
            $data["operate_id"] = $this->parent_id;
            $data["is_tag"] = BusinessCardCustomer::IS_TAG_NO;
            $result = BusinessCardCustomer::operateData($data);
            if($result === false){
                return false;
            }
        }
        \Yii::warning("------BusinessCardCustomerJob end---------");
        return true;
    }
}