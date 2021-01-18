<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 标签
 * Author: zal
 * Date: 2020-08-08
 * Time: 09:34
 */

namespace app\forms\api\tag;

use app\core\ApiCode;
use app\forms\common\tag\TagCommon;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\GoodsCats;
use app\models\ObjectTag;
use app\plugins\mch\models\Mch;

class TagForm extends BaseModel
{
    public $cat_id = 1;
    public $object_id;
    public $tag_id;
    public function rules()
    {
        return [
            [['cat_id', 'object_id', 'tag_id'], 'integer'],
            [['object_id', 'tag_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * 获取用户标签
     * @return array
     */
    public function getUserTag()
    {
        if(empty($this->object_id)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }
        $returnData = TagCommon::getObjectTag($this->object_id,$this->cat_id,10,1);
        return $returnData;
    }

}