<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 改变上级
 * Author: zal
 * Date: 2020-06-11
 * Time: 16:11
 */

namespace app\component\jobs;

use app\logic\CommonLogic;
use app\logic\UserLogic;
use app\models\Mall;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property User $user
 * @property Mall $mall
 */
class ChangeSuperiorJob extends BaseObject implements JobInterface
{
    public $mall;
    public $user;
    public $user_id;
    public $beforeParentId;// 变更前的上级id
    public $parentId; // 变更后的上级id
    private $level;

    public function execute($queue)
    {

    }
}
