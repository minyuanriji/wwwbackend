<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台商城-版权
 * Author: zal
 * Date: 2020-04-22
 * Time: 19:10
 */

namespace app\forms\admin\mall;

use app\core\ApiCode;
use app\forms\admin\MallForm;
use app\logic\OptionLogic;
use app\models\Option;
use yii\helpers\HtmlPurifier;

class MallCopyrightForm extends MallForm
{
    public $description;
    public $link_url;
    public $pic_url;
    public $type;
    public $mobile;
    public $link;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['description', 'link_url', 'pic_url', 'mobile', 'type'], 'trim'],
            [['description', 'link_url', 'pic_url', 'mobile', 'type'], function ($attribute, $params) {
                $this->$attribute = HtmlPurifier::process($this->$attribute);
            }],
            [['link'], 'safe']
        ]);
    }

    /**
     * 保存版权
     * @return array
     */
    public function saveCopyright($id)
    {
        try {
            $permission = \Yii::$app->role->permission;
            $showCopyright = is_array($permission) ? in_array('copyright', $permission) : $permission;
            if (!$showCopyright) {
                throw new \Exception('无权限修改');
            }
            $mall = $this->getOneMall($id);
            $data = $this->getAttributes(['description', 'link_url', 'pic_url', 'mobile', 'type', 'link']);
            $res = OptionLogic::set(Option::NAME_COPYRIGHT, $data, $mall->id, Option::GROUP_APP);
            if (!$res) {
                throw new \Exception('保存失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
