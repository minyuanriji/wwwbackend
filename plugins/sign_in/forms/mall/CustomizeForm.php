<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台页面控制类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\Coupon;
use app\plugins\sign_in\models\MemberLevel;
use app\plugins\sign_in\models\SignInAwardConfig;
use app\plugins\sign_in\models\User;
use function GuzzleHttp\default_user_agent;

class CustomizeForm extends BaseModel
{
    public $remind_font;
    public $daily_font;
    public $prompt_font;
    public $btn_bg;
    public $not_prompt_font;
    public $not_btn_bg;
    public $line_font;
    public $end_bg;
    public $end_style;
    public $not_signed_icon;
    public $signed_icon;
    public $head_bg;
    public $balance_icon;
    public $integral_icon;
    public $calendar_icon;
    public $end_gradient_bg;
    public $limit = 10;
    public $page = 1;
    public $keyword;
    public $kw_type;
    public $level;
    public $start_date;
    public $end_date;


    public function rules()
    {
        return [
            [['end_gradient_bg','remind_font', 'daily_font', 'prompt_font', 'btn_bg', 'not_prompt_font', 'not_btn_bg', 'line_font', 'end_bg', 'not_signed_icon', 'signed_icon', 'head_bg', 'balance_icon', 'integral_icon', 'calendar_icon'], 'string'],
            [['end_gradient_bg','remind_font', 'daily_font', 'prompt_font', 'btn_bg', 'not_prompt_font', 'not_btn_bg', 'line_font', 'end_bg', 'not_signed_icon', 'signed_icon', 'head_bg', 'balance_icon', 'integral_icon', 'calendar_icon'], 'default', 'value' => ''],
            [['end_style'], 'integer'],
            [['end_style'], 'default', 'value' => 0],
            [['limit', 'page'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'kw_type'], 'string'],
            [['keyword'], 'string', 'max' => 255],
            [['level'],'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'remind_font' => '签到提醒字体颜色',
            'daily_font' => '今日签到字体颜色',
            'prompt_font' => '已领取字体颜色',
            'btn_bg' => '已领取按钮颜色',
            'not_prompt_font' => '未领取字体颜色',
            'not_btn_bg' => '未领取按钮颜色',
            'line_font' => '分割线颜色',
            'end_bg' => '下半部背景颜色',
            'end_style' => '下半部颜色配置',
            'not_signed_icon' => '未签到图标',
            'signed_icon' => '已签到图标',
            'head_bg' => '头部背景图',
            'balance_icon' => '红包图标',
            'integral_icon' => '积分图标',
            'calendar_icon' => '日历签到图标',
            'end_gradient_bg' => '渐变颜色',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $config = Common::getCommon(\Yii::$app->mall)->getCustomize();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => $config
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };
        $config = Common::getCommon(\Yii::$app->mall)->setCustomize($this->attributes);
        if ($config) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($config);
        }
    }

    public function getCouponList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $mall = \Yii::$app->mall;
        $list = Coupon::getData(['mall_id' => $mall->id,"limit" => $this->limit,"page" => $this->page,"sort_key" => "sort","sort_val" => " asc"]);

        $newList = [];
        /* @var Distribution[] $list */
        foreach ($list["list"] as $item) {
            /* @var User $user */
            $item["created_at"] = date("Y-m-d H:i:s",$item["created_at"]);
            $newList[] = $item;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $list["pagination"],
            ]
        ];

    }

    public function getUserList(){
        $mall_id = \Yii::$app->mall->id;

        $query = User::find()->where([
            'mall_id' => $mall_id,
        ]);

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type)
            {
                case "user_id":
                    $query->andWhere(['id' => $this->keyword]);
                    break;
                case "mobile":
                    $query->andWhere(['mobile' => $this->keyword]);
                    break;
                case "nickname":
                    $query->andWhere(['like', 'nickname', $this->keyword]);
                    break;
                default:
            }
        }

        //搜索
        $query->keyword($this->level, ['level' => $this->level]);

        $params["limit"] = $this->limit;
        $params["page"]  = $this->page;

        $list = $query->andWhere(['and', ['IS NOT', 'mobile', NULL], ['!=', 'mobile', ''], ['is_delete' => 0]])
            ->select(['id','nickname','avatar_url','username','mobile','level'])
            ->orderBy('id DESC')
            ->page($pagination, $params["limit"], $params["page"])
            ->asArray()
            ->all();

        //获取到用户积分以及其他信息
        $common = new Common();
        $list = $common->getUserList($list,\Yii::$app->mall->id);

        $mall_members = MemberLevel::findAll(['mall_id' => $mall_id, 'status' => 1, 'is_delete' => 0]);
        if(isset($params["limit"]) && isset($params["page"])) {
            $returnData["list"] = $list;
            $returnData["pagination"] = $pagination;
            $returnData["mall_members"] = $mall_members;
        }else{
            $returnData = $list;
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $returnData);
    }


    public function getLevelList()
    {

        $query = MemberLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

/*
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }*/
        $list = $query->page($pagination)->orderBy(['level' => SORT_ASC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
