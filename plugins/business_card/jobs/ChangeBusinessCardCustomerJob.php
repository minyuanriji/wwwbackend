<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 改变了上级，执行名片客户表的operate_id更改任务
 * Author: zal
 * Date: 2020-07-23
 * Time: 18:35
 */

namespace app\plugins\business_card\jobs;

use app\logic\CommonLogic;
use app\models\Mall;
use app\plugins\business_card\models\BusinessCardCustomer;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class ChangeBusinessCardCustomerJob extends Component implements JobInterface
{
    public $user_id;
    public $parent_id;
    public $before_parent_id;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("------ChangeBusinessCardCustomerJob start---------");
        \Yii::warning("ChangeBusinessCardCustomerJob user_id={$this->user_id},parent_id={$this->parent_id},before_parent_id={$this->before_parent_id},mall_id={$this->mall_id}");
        try{
            \Yii::$app->setMall(Mall::findOne($this->mall_id));
            $params = [];
            $params["user_id"] = $this->user_id;
            $params["mall_id"] = $this->mall_id;
            //$params["operate_id"] = $this->before_parent_id;
            $params["is_one"] = 1;
            /** @var BusinessCardCustomer $businessCardCustomer */
            $businessCardCustomer = BusinessCardCustomer::getData($params);
            \Yii::warning("ChangeBusinessCardCustomerJob businessCardCustomer=".var_export($businessCardCustomer,true));
            if(!empty($businessCardCustomer)){
                $updateCardCustomerData = [];
                $updateCardCustomerData["operate_id"] = $this->parent_id;
                $updateCardCustomerData["id"] = $businessCardCustomer["id"];
                $result = BusinessCardCustomer::operateData($updateCardCustomerData);
            }else{
                $data = [];
                $data["mall_id"] = $this->mall_id;
                $data["user_id"] = $this->user_id;
                $data["operate_id"] = $this->parent_id;
                $data["is_tag"] = BusinessCardCustomer::IS_TAG_NO;
                $result = BusinessCardCustomer::operateData($data);
            }
            if($result === false){
                return false;
            }
        }catch (\Exception $ex){
            \Yii::error("ChangeBusinessCardCustomerJob error ".CommonLogic::getExceptionMessage($ex));
            return false;
        }
        \Yii::warning("------ChangeBusinessCardCustomerJob end---------");
        return true;
    }
}