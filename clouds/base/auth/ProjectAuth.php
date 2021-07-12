<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:27
 */
namespace app\clouds\auth;


use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\project\Project;

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

    }
}