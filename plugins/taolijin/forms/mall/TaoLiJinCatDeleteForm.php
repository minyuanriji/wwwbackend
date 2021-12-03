<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatDeleteForm extends BaseModel {

    public $id;

    public function rules(){
        return [
            [['id'], 'integer']
        ];
    }

    public function destroy(){

        $t = \Yii::$app->db->beginTransaction();

        try {

            $listForm = new TaoLiJinCatListForm();
            $list = $listForm->getDefault($this->id, true, false);

            if (empty($list)) {
                throw new \Exception('无效的分类');
            }

            $catIdList = $this->getCatId($list);
            TaolijinCats::updateAll(
                [
                    'deleted_at' => date('Y-m-d H:i:s', time()),
                    'is_delete' => 1,
                ],
                ['id' => $catIdList]
            );

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功',
            ];

        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array $list
     * @return array
     * 获取list中及其下级的所有id
     */
    public function getCatId($list){
        $catId = [];
        if (empty($list)) {
            return $catId;
        }
        foreach ($list as $item) {
            $catId[] = $item['id'];
            if (!empty($item['child'])) {
                $catId = array_merge($catId, $this->getCatId($item['child']));
            }
        }
        return $catId;
    }
}