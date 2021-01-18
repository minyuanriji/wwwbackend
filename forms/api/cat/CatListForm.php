<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-29
 * Time: 9:34
 */

namespace app\forms\api\cat;


use app\core\ApiCode;
use app\events\StatisticsEvent;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\GoodsCats;
use app\models\StatisticsBrowseLog;
use app\plugins\mch\models\Mch;

class CatListForm extends BaseModel
{
    public $cat_id;
    public $mch_id;
    public $select_cat_id;
    public function rules()
    {
        return [
            [['cat_id', 'mch_id', 'select_cat_id'], 'integer'],
            [['cat_id', 'mch_id', 'select_cat_id'], 'default', 'value' => 0],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
         $this->mch_id = 0;
        try {
            if ($this->mch_id && empty(Mch::findOne($this->mch_id))) {
                throw new \Exception('多商户不存在');
            }
            /**********start*************/
            $mall_cat_style_a = 6;
            $mall_cat_style_b = 7;
            $mall_cat_style_c = 11;
            $cat_style = AppConfigLogic::getAppCatStyle()['cat_style'];
            if (in_array($cat_style, [$mall_cat_style_a, $mall_cat_style_b, $mall_cat_style_c])) {
                $select_cat_id = 0;
                $this->cat_id = $this->select_cat_id ?: $this->cat_id;
                $select_cat_id ^= $this->cat_id;
                $this->cat_id ^= $select_cat_id;
                $select_cat_id ^= $this->cat_id;
            } else {
                $select_cat_id = $this->select_cat_id;
            }
            /**********end*************/
            $list = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'parent_id' => $this->cat_id,
                'is_delete' => 0,
                'mch_id' => $this->mch_id,
                'status' => 1,
                'is_show' => 1,
            ])->with(['child' => function ($query) {
                $query->with(['child' => function ($query) {
                    $query->andWhere(['mch_id' => $this->mch_id, 'status' => 1, 'is_show' => 1])->orderBy('sort ASC');
                }])->andWhere(['mch_id' => $this->mch_id, 'status' => 1, 'is_show' => 1])->orderBy('sort ASC');
            }])
                ->orderBy('sort ASC')
                ->asArray()
                ->all();
            $func = function ($data) use (&$func) {
                if ($this->mch_id) {
                    $data['page_url'] = sprintf("/plugins/mch/shop/shop?mch_id=%u&cat_id=%u", $this->mch_id, $data['id']);
                } else {
                    $data['page_url'] = sprintf("/pages/goods/list?cat_id=%u", $data['id']);
                }
                $data['advert_params'] = \yii\helpers\BaseJson::decode($data['advert_params']);
                if (isset($data['child'])) {
                    foreach ($data['child'] as $key => $item) {
                        $data['child'][$key] = array_merge($func($item), ['active' => $key === 0]);
                    }
                }
                return $data;
            };

            foreach ($list as $k => $v) {
                $list[$k] = array_merge($func($v), ['active' => $k === 0]);
            }

            //temp
            if (!empty($select_cat_id) && !empty($list)) {
                $func = function ($item) use (&$func, $select_cat_id) {
                    if ($item['id'] == $select_cat_id) {
                        return true;
                    }
                    if (isset($item['child'])) {
                        foreach ($item['child'] as $key => $item) {
                            return $func($item);
                        }
                    };
                    return false;
                };
                $sentinel = true;
                foreach ($list as $k => $item) {
                    $list[$k]['active'] = $func($item);
                    $list[$k]['active'] && $sentinel = false;
                }
                $sentinel && $list[0]['active'] = $sentinel;
            }
            \Yii::$app->trigger(StatisticsBrowseLog::EVEN_STATISTICS_LOG, new StatisticsEvent(['mall_id'=>\Yii::$app->mall->id,'browse_type'=>1,'user_id'=>\Yii::$app->user->id,'user_ip'=>$_SERVER['REMOTE_ADDR']]) );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'cat_style' => $cat_style,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}