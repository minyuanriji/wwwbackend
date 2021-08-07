<?php


namespace app\forms\api\payCenter\giftpacks;


use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;

trait CommonPayGroup{

    public $group_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['group_id'], 'required']
        ]);
    }

    /**
     * 获取拼单信息
     * @return GiftpacksGroup
     * @throws \Exception
     */
    private function getGiftpacksGroup(){
        static $groups;
        if(!isset($groups[$this->group_id])){
            $groups[$this->group_id] = GiftpacksGroup::findOne($this->group_id);
            if(!$groups[$this->group_id]){
                throw new \Exception("拼单信息不存在");
            }
        }
        return $groups[$this->group_id];
    }

    /**
     * 获取大礼包
     * @return Giftpacks
     * @throws \Exception
     */
    private function getGiftpacks(){
        static $datas;
        $group = $this->getGiftpacksGroup();
        if(!isset($datas[$group->pack_id])){
            $datas[$group->pack_id] = Giftpacks::findOne($group->pack_id);
            if(!$datas[$group->pack_id] || $datas[$group->pack_id]->is_delete){
                throw new \Exception("大礼包不存在");
            }
        }
        return $datas[$group->pack_id];
    }
}