<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\Admin;
use app\models\DistrictArr;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;
use phpDocumentor\Reflection\Types\Null_;

class MchForm extends BaseModel
{
    public $keyword;
    public $keyword1;
    public $page;
    public $id;
    public $switch_type;
    public $password;
    public $sort;
    public $level;
    public $address;
    public $sort_prop;
    public $sort_type;
    public $account;

    public function rules()
    {
        return [
            [['keyword', 'keyword1', 'switch_type', 'password', 'sort_prop', 'sort_type', 'account'], 'string'],
            [['id', 'sort', 'level'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['address'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序'
        ];
    }

    public function getList()
    {
        $form = new CommonMchForm();
        $form->keyword = $this->keyword;
        $form->keyword1 = $this->keyword1;
        $form->sort_type = $this->sort_type;
        $form->sort_prop = $this->sort_prop;
        $res = $form->getList();
        $url = \Yii::$app->urlManager->createAbsoluteUrl('mch/admin/login');

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
                'url' => urldecode($url),
            ]
        ];
    }

    public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                //'id' => \Yii::$app->user->identity->mch_id ?: $this->id,
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo', 'mchAdmin', 'store', 'category')->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['latitude_longitude'] = $detail['store']['longitude'] && $detail['store']['latitude'] ?
                $detail['store']['latitude'] . ',' . $detail['store']['longitude'] : '';

            $detail['lng'] = $detail['store']['longitude'] ?? '';
            $detail['lat'] = $detail['store']['latitude'] ?? '';

            $detail['address'] = $detail['store']['address'];
            $detail['logo'] = $detail['store']['cover_url'];
            $detail['service_mobile'] = $detail['store']['mobile'];
            //$detail['bg_pic_url'] = \Yii::$app->serializer->decode($detail['store']['pic_url']);
            $bgPicUrls = @json_decode($detail['store']['pic_url'], true);
            $detail['bg_pic_url'] = is_array($bgPicUrls) ? $bgPicUrls : [];
            $detail['name'] = $detail['store']['name'];
            $detail['description'] = $detail['store']['description'];
            $detail['scope'] = $detail['store']['scope'];
            $detail['district'] = [
                (int)$detail['store']['province_id'],
                (int)$detail['store']['city_id'],
                (int)$detail['store']['district_id']
            ];
            try {
                $detail['districts'] = DistrictArr::getDistrict((int)$detail['store']['province_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['city_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['district_id'])['name'];
            } catch (\Exception $e) {
                $detail['districts'] = '';
            }
            $detail['cat_name'] = $detail['category']['name'];
            $detail['form_data'] = $detail['form_data'] ? \Yii::$app->serializer->decode($detail['form_data']) : [];
            $detail['username'] = $detail['mchAdmin']['username'];
            $detail['password'] = $detail['mchAdmin']['password'];
            $detail['admin_id'] = $detail['mchAdmin']['id'];

            //编辑购物券赠送所需参数
            $detail['give_shopping_params'][0]['id'] = $this->id;
            $detail['give_shopping_params'][0]['mall_id'] = \Yii::$app->mall->id;
            $detail['give_shopping_params'][0]['store_id'] = $detail['store']['id'];
            $detail['give_shopping_params'][0]['name'] = $detail['store']['name'];
            $detail['give_shopping_params'][0]['cover_url'] = $detail['store']['cover_url'];

            //获取购物券赠送比列
            $shoppingVoucherResult = ShoppingVoucherFromStore::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "store_id" => $detail['store']['id']
            ]);
            if ($shoppingVoucherResult) {
                $detail['give_shopping_voucher']['give_value'] = $shoppingVoucherResult->give_value;
                $detail['give_shopping_voucher']['start_at'] = date('Y-m-d', $shoppingVoucherResult->start_at);
                $detail['give_shopping_voucher']['enable'] = $shoppingVoucherResult->is_delete ? false : true;
            } else {
                $detail['give_shopping_voucher']['give_value'] = 0;
                $detail['give_shopping_voucher']['start_at'] = 0;
                $detail['give_shopping_voucher']['enable'] = false;
            }

            //获取积分赠送比例
            $scoreResult = ScoreFromStore::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "store_id" => $detail['store']['id']
            ]);
            $scoreGiveSettings = [
                "is_permanent" => 0,
                "integral_num" => 0,
                "period"       => 1,
                "period_unit"  => "month",
                "expire"       => 30
            ];
            if ($scoreResult) {
                $detail['give_score']['start_at'] = date("Y-m-d H:i:s", $scoreResult->start_at ?: time());
                $detail['give_score']['score_give_settings'] = array_merge($scoreGiveSettings,
                    !empty($scoreResult->score_setting) ? (array)@json_decode($scoreResult->score_setting, true) : []);
                $detail['give_score']['score_give_settings']['is_permanent'] = (int)$detail['give_score']['score_give_settings']['is_permanent'];
                $detail['give_score']['rate'] = (float)$scoreResult->rate;
                $detail['give_score']['score_enable'] = $scoreResult->enable_score > 0 ? true : false;
            } else {
                $detail['give_score']['rate'] = 0;
                $detail['give_score']['start_at'] = 0;
                $detail['give_score']['score_give_settings'] = [];
                $detail['give_score']['score_enable'] = false;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var Mch $model */
            $model = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$model) {
                throw new \Exception('商户不存在');
            }

            $bindUserId = $model->user_id;

            $model->is_delete = 1;
            $model->user_id   = 0;
            $model->mobile   = null;
            if (!$model->save()) {
                throw new \Exception($this->responseErrorMsg($model));
            }

            //删除后台登陆账号
            $admin = Admin::find()->where(['mch_id' => $model->id])->one();
            $admin && $admin->delete();

            //清空用户关联的商户ID
            User::updateAll(["mch_id" => 0], ["mch_id" => $model->id]);

            //修改资料审核状态
            MchApply::updateAll([
                "status"     => "applying",
                "updated_at" => time()
            ], ["user_id" => $bindUserId]);

            //清理连锁店信息
            MchGroup::deleteAll(["mch_id" => $model->id]);
            MchGroupItem::deleteAll(["mch_id" => $model->id]);

            $transaction->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');

        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    public function switchStatus()
    {
        try {
            /** @var Mch $detail */
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            if ($this->switch_type == 'status') {
                $detail->status = $detail->status ? 0 : 1;
            }
            if ($this->switch_type == 'is_recommend') {
                $detail->is_recommend = $detail->is_recommend ? 0 : 1;
            }
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function route()
    {
        $mallId = base64_encode(\Yii::$app->mall->id);
        $url = \Yii::$app->urlManager->createAbsoluteUrl('mch/admin/login');
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => urldecode($url),
            ]
        ];
    }

    public function updatePassword()
    {
        try {
            if(empty($this->account))
                throw new \Exception('请填写新账号');

            if (!$this->password)
                throw new \Exception('请填写新密码');

            $admin = Admin::find()->where([
                'username' => $this->account,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();

            if ($admin && $admin->mch_id != $this->id) {
                throw new \Exception('商户账号已存在！');
            }

            if ($this->password) {
                if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $this->password) > 0) {
                    throw new \Exception('密码不能包含中文字符');
                }
            }

            // 商户账号创建
            $admin = Admin::findOne(['mch_id' => $this->id]);
            if (!$admin) {
                $admin = new Admin();
                $admin->mch_id          = $this->id;
                $admin->mall_id         = \Yii::$app->mall->id;
                $admin->auth_key        = \Yii::$app->security->generateRandomString();
                $admin->access_token    = \Yii::$app->security->generateRandomString();
                $admin->admin_type      = Admin::ADMIN_TYPE_OPERATE;
            }

            $admin->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $admin->username = $this->account;
            if (!$admin->save()) {
                throw new \Exception($this->responseErrorMsg($admin));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '账号密码更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function searchUser()
    {
        $keyword = trim($this->keyword);
        $query = User::find()->alias('u')->select('u.id,u.nickname,u.avatar_url as avatar');
        $query->andWhere(['u.mall_id' => \Yii::$app->mall->id]);
        $query->andWhere([
            "OR",
            ['LIKE', 'u.nickname', $keyword],
            ['u.id' => $keyword]
        ]);
        
        $list = $query->InnerJoinwith('userInfo')->orderBy('nickname')->limit(10)->asArray()->all();
        //
        /* foreach ($list as $k => $v) {
            $list[$k]['avatar'] = $v['userInfo']['avatar'];
        } */
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function editSort()
    {
        try {
            $mch = Mch::findOne($this->id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            $mch->sort = $this->sort;
            $res = $mch->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($mch));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getCount()
    {
        $count = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'review_status' => 0,
        ])->count();

        return $count;
    }

    public function getMchList()
    {
        $query = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'review_status' => 1,
        ]);

        if ($this->keyword) {
            $mchIds = Store::find()->where(['like', 'name', $this->keyword])->select('mch_id');
            $userIds = User::find()->where(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id');
            $query->andWhere([
                'or',
                ['id' => $mchIds],
                ['user_id' => $userIds],
            ]);
        }
        if (is_array($this->address) && $this->address) {
            $add_count = count($this->address);
            if ($add_count == 1) {
                $where = ['province_id' => $this->address[0]];
            } elseif ($add_count == 2) {
                $where = ['province_id' => $this->address[0], 'city_id' => $this->address[1]];
            } elseif ($add_count == 3) {
                $where = ['province_id' => $this->address[0], 'city_id' => $this->address[1], 'district_id' => $this->address[2]];
            } else {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '参数错误，请重新选择地区');
            }
            $mch_ids = Store::find()->where($where)->select('mch_id');
            $query->andWhere([
                'id' => $mch_ids,
            ]);
        }

        $list = $query->orderBy(['sort' => SORT_ASC])
            ->with('user', 'store', 'category')
            ->page($pagination)->asArray()->all();

        if($list){
            foreach($list as &$item){
                $item['name'] = $item['store']['name'];
            }
        }

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
