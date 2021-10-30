<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\shopping_voucher\forms\mall\FromStoreSearchStoreForm;

class FromStoreBatchSaveForm extends BaseModel{

    public $list;
    public $is_all;
    public $do_page;
    public $do_search;
    public $give_type;
    public $give_value;
    public $start_at;
    public $score_give_settings;
    public $score_enable;
    public $rate;

    public function rules(){
        return [
            [['is_all', 'give_type', 'give_value', 'start_at'], 'required'],
            [['do_page'], 'integer'],
            [['list', 'do_search', 'score_give_settings', 'score_enable', 'rate'], 'safe'],
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if($this->is_all){
                $form = new FromStoreSearchStoreForm();
                $form->attributes = $this->do_search;
                $form->page = $this->do_page;
                $res = $form->getList();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                $list = $res['data']['list'];
            }else{
                $list = $this->list;
            }

            if($list){
                foreach($list as $item){
                    $exists = ScoreFromStore::findOne([
                        "store_id" => $item['store_id'],
                        "mall_id" => $item['mall_id'],
                    ]);

                    if(!$exists){
                        $fromStore = new ScoreFromStore([
                            "mall_id"    => \Yii::$app->mall->id,
                            "created_at" => time()
                        ]);
                    }else{
                        $fromStore = $exists;
                        $fromStore->is_delete = 0;
                        $fromStore->deleted_at = 0;
                    }

                    $this->score_give_settings['integral_num'] = 0;
                    if(isset($this->score_give_settings['is_permanent']) && $this->score_give_settings['is_permanent']){
                        $this->score_give_settings['expire'] = -1;
                        $this->score_give_settings['period'] = 1;
                    }else{
                        $this->score_give_settings['expire'] = max(0, min($this->score_give_settings['expire'], 31));
                    }

                    $fromStore->mch_id        = $item['id'];
                    $fromStore->store_id      = $item['store_id'];
                    $fromStore->updated_at    = time();
                    $fromStore->name          = $item['name'];
                    $fromStore->cover_url     = $item['cover_url'] ?: 'https://www.mingyuanriji.cn/web/static/header-logo.png';
                    $fromStore->start_at      = strtotime($this->start_at);
                    $fromStore->enable_score  = $this->score_enable == "true" ? 1 : 0;
                    $fromStore->score_setting = is_array($this->score_give_settings) ? json_encode($this->score_give_settings) : '';
                    $fromStore->rate          = min(max(0, (float)$this->rate), 100);

                    if(!$fromStore->save()){
                        throw new \Exception($this->responseErrorMsg($fromStore));
                    }
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}