<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\forms\mall\export\IncomeLogExport;
use app\helpers\CityHelper;
use app\helpers\SerializeHelper;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\IncomeLog;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCheckoutOrder;

class MchIncomeLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $mch_id;
    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['page', 'limit', 'mch_id'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'flag', 'kw_type'], 'trim'],
            [['fields'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = MchAccountLog::find()->alias('ma')
            ->where(['ma.mall_id' => \Yii::$app->mall->id])
            ->leftJoin(['m' => Mch::tableName()], 'ma.mch_id=m.id')
            ->leftJoin(['s' => Store::tableName()], 'ma.mch_id=s.mch_id');

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type)
            {
                case "mch_id":
                    $query->andWhere(['ma.mch_id' => $this->keyword]);
                    break;
                case "mobile":
                    $query->andWhere(['m.mobile' => $this->keyword]);
                    break;
                case "store_name":
                    $query->andWhere(['like', 's.name', $this->keyword]);
                    break;
                default:
            }
        }

        if ($this->mch_id) {
            $query->andWhere(['ma.mch_id' => $this->mch_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'ma.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'ma.created_at', strtotime($this->start_date)]);
        }
        $query->select(['ma.*','s.name as store_name','s.cover_url']);
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new IncomeLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'b.');
            return false;
        }
        $list = $query->page($pagination, $this->limit)->orderBy('ma.id desc')->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                if (empty($item['cover_url'])) {
                    $item['cover_url'] = \Yii::$app->params['store_default_avatar'];
                }
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
//            'export_list' => (new IncomeLogExport())->fieldsList(),
            'pagination' => $pagination
        ]);
    }
}