<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\Admin;
use app\models\DistrictArr;
use app\models\BaseModel;
use app\models\EfpsMerchantMcc;
use app\models\EfpsMchReviewInfo;
use app\models\User;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Mch;

class MchForm extends BaseModel
{
    public $keyword;
    public $page;
    public $id;
    public $switch_type;
    public $password;
    public $sort;

    public function rules()
    {
        return [
            [['keyword', 'switch_type', 'password'], 'string'],
            [['id', 'sort'], 'integer'],
            [['page'], 'default', 'value' => 1],
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
        $res = $form->getList();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination']
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

            $relatEfps = EfpsMchReviewInfo::findOne(["mch_id" => $this->id]);
            if(!$relatEfps){
                $relatEfps = new EfpsMchReviewInfo();
                $relatEfps->mch_id        = $this->id;
                $relatEfps->register_type = "separate_account";
                $relatEfps->created_at    = time();
                $relatEfps->updated_at    = time();
                if(!$relatEfps->save()){
                    throw new \Exception($this->responseErrorMsg($relatEfps));
                }
            }

            $reviewData = ArrayHelper::toArray($relatEfps);
            if(!empty($reviewData)){
                $reviewData['acceptOrder'] = $reviewData['acceptOrder'] ? "1" : "0";
                $reviewData['openAccount'] = $reviewData['openAccount'] ? "1" : "0";
                $reviewData['paper_merchantType'] = (string)$reviewData['paper_merchantType'];
                $reviewData['paper_isCc'] = $reviewData['paper_isCc'] ? "1" : "0";
                $reviewData['paper_settleAccountType'] = (string)$reviewData['paper_settleAccountType'];
                $reviewData['paper_settleTarget'] = (string)$reviewData['paper_settleTarget'];
            }
            $reviewData['paper_mcc_obj'] = ["type" => "", "code" => ""];
            if(!empty($reviewData['paper_mcc'])){
                $mcc = EfpsMerchantMcc::findOne(['code' => $reviewData['paper_mcc']]);
                if($mcc){
                    $reviewData['paper_mcc_obj'] = [
                        "type" => $mcc->type,
                        "code" => (string)$mcc->code
                    ];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'review' => $reviewData
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

            $model->is_delete = 1;
            $model->user_id   = 0;
            if (!$model->save()) {
                throw new \Exception($this->responseErrorMsg($model));
            }

            //删除后台登陆账号
            $admin = Admin::find()->where(['mch_id' => $model->id])->one();
            $admin && $admin->delete();

            /** @var User $user */
            $user = User::find()->where(['mch_id' => $model->id])->one();
            if (!$user) {
                throw new \Exception('商户账号不存在');
            }

            $user->mch_id = 0;
            if (!$user->save()) {
                throw new \Exception($this->responseErrorMsg($user));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
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
            if (!$this->password) {
                throw new \Exception('请填写新密码');
            }
            $admin = Admin::find()->where([
                'mch_id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$admin) {
                throw new \Exception('商户账号不存在');
            }

            $admin->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $admin->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($admin));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '密码更新成功',
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
}
