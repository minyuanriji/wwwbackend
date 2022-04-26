<?php

namespace app\plugins\smart_shop\components;

use app\plugins\smart_shop\forms\mall\SettingDetailForm;
use yii\base\Component;
use yii\db\Connection;

class SmartShop extends Component
{
    public $db = null;
    public $setting = [];

    use SmartShopOrderTrait;
    use SmartShopShopTrait;
    use SmartShopIdentityTrait;

    public function init()
    {
        parent::init();
        $this->initSetting();
    }

    public function getDB($force = false){
        $this->db = new Connection([
            'dsn'         => 'mysql:host=' . $this->setting['db_host'] . ';port=' . $this->setting['db_port'] . ';dbname=' . $this->setting['db_name'],
            'username'    => $this->setting['db_user'],
            'password'    => $this->setting['db_pass'],
            'charset'     => $this->setting['db_charset'],
            'tablePrefix' => $this->setting['db_tb_prefix']
        ]);
        return $this->db;
    }

    public function initSetting(){
        $this->setting = SettingDetailForm::getSetting();
    }


}