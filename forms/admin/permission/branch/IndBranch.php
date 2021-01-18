<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 09:55
 */

namespace app\forms\admin\permission\branch;

class IndBranch extends BaseBranch
{
    public $ignore = 'ind';

    public function deleteMenu($menu)
    {
        if (isset($menu['ignore']) && in_array($this->ignore, $menu['ignore'])) {
            return true;
        }
        return false;
    }

    public function logoutUrl()
    {
        return \Yii::$app->urlManager->createUrl('admin/index/index');
    }
}
