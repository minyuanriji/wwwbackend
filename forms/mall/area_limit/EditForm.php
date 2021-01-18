<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 15:46
 */

namespace app\forms\mall\area_limit;


use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class EditForm extends BaseModel
{

    public $is_enable;
    public $detail;

    public function rules()
    {
        return [
            ['is_enable', 'integer'],
            ['detail', 'safe']
        ];
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-17
     * @Time: 15:46
     * @Note:
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $data = [
            'is_enable' => $this->is_enable,
            'detail' => $this->detail
        ];
        $res = OptionLogic::set(
            Option::NAME_TERRITORIAL_LIMITATION,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->admin->identity->mch_id
        );
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败'
            ];
        }
    }

}