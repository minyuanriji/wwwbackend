<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:27
 */
namespace app\clouds\base\auth;


use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\project\Project;
use app\clouds\base\tables\CloudAuthProjectList;

class ProjectAuth extends Auth
{
    private $project;

    public function __construct(Project $project, $config = [])
    {
        parent::__construct($config);
        $this->project = $project;
    }

    /**
     * 是否授权成功
     * @return boolean
     */
    public function pass()
    {
        $access = false;
        $identity = IdentityHelper::getIdentity();
        if($this->project->getModel()->security == "public" || ($identity && $identity->getId() == $this->project->getModel()->author_id)){
            $access = true;
        }elseif($identity && $this->project->getModel()->security == "authorize"){
            $authItem = CloudAuthProjectList::findOne([
                "target_id" => $this->project->getModel()->id,
                "user_id"   => $identity->getId()
            ]);
            if($authItem){
                $access = true;
            }
        }
        return $access;
    }
}