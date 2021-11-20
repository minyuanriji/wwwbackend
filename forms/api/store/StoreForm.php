<?php

namespace app\forms\api\store;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\EfpsMchReviewInfo;
use app\models\EfpsMerchantMcc;
use app\models\Store;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\mch\forms\api\MchApplyOperationLogSaveForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchApplyOperationLog;
use app\plugins\mch\models\MchCommonCat;
use app\plugins\mch\forms\common\apply\MchApplyRefuseForm;
use app\plugins\mch\forms\common\apply\MchApplyPassForm;
use yii\base\BaseObject;

class StoreForm extends BaseModel
{
    public $page;
    public $id;
    public $status;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        $user_res = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_res) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户不存在',
            ];
        }
        $user_link = UserRelationshipLink::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_link) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '关系链异常，请联系客服！'
            ];
        }
        $user_ids = UserRelationshipLink::find()
            ->select('user_id')
            ->andWhere([
                "AND",
                "`left` > '$user_link->left' AND `right` < '$user_link->right'"
            ])
            ->asArray()
            ->all();
        $list = [];
        $pagination = null;
        if ($user_ids) {
            $new_user_ids = array_column($user_ids,'user_id');
            $query = MchApply::find()->alias("ma")->where([
                'ma.mall_id' => \Yii::$app->mall->id,
                'ma.status' => $this->status,
            ]);

            if ($this->status == 'verifying') {
                $query->andWhere(['ma.is_special_discount' => 0]);
            }
            $query->leftJoin(["u" => User::tableName()], "u.id=ma.user_id");
            $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");
            $query->andWhere(['in','ma.user_id',$new_user_ids]);

            if ($this->keyword) {
                $query->where(['like', 'ma.mobile', $this->keyword]);
                $query->orWhere(['like', 'ma.json_apply_data', $this->keyword]);
            }

            $list = $query->select([
                        "ma.*",
                        "DATE_FORMAT(FROM_UNIXTIME(ma.created_at),'%Y-%m-%d %H:%i:%s') as created_at",
                        "p.id as parent_id", "p.nickname as parent_nickname",
                        "p.mobile as parent_mobile",
                        "( CASE p.role_type WHEN 'store' THEN 'VIP会员' WHEN 'partner' THEN '合伙人' WHEN 'branch_office' THEN '分公司' WHEN 'user' THEN '普通用户' END ) AS 'parent_role_type'"
                    ])
                    ->orderBy(['ma.created_at' => SORT_DESC])
                    ->page($pagination,10)
                    ->asArray()
                    ->all();
        }
        if ($list) {
            foreach ($list as &$item) {
                $apply_data = SerializeHelper::decode($item['json_apply_data']);
                $item['store_name'] = $apply_data['store_name'];
                $item['store_logo'] = 'http://mingyuanriji.cn/web/static/header-logo.png';
                unset($item['json_apply_data'],$apply_data);
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

    public function destroy()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getDetail()
    {
        try {
            $detail = MchApply::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ])->one();
            if (!$detail)
                throw new \Exception('商户不存在');

            $apply_data = SerializeHelper::decode($detail->json_apply_data);

            $mch_common_cat_name = MchCommonCat::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => $apply_data['store_mch_common_cat_id'],
            ])->asArray()->one();
            $apply_data['store_mch_common_cat_name'] = $mch_common_cat_name ? $mch_common_cat_name['name'] : '';

            try {
                $apply_data['districts'] = DistrictArr::getDistrict((int)$apply_data['store_province_id'])['name'] .
                    DistrictArr::getDistrict((int)$apply_data['store_city_id'])['name'] .
                    DistrictArr::getDistrict((int)$apply_data['store_district_id'])['name'];
            } catch (\Exception $e) {
                $apply_data['districts'] = '';
            }

            unset($detail->json_apply_data);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'apply_data' => $apply_data,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getDetailOld()
    {
        try {
            $detail = Mch::find()->where([
                        'id' => $this->id,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                     ])
                    ->with([
                        'user',
                        'mchAdmin',
                        'store',
                        'category'
                    ])
                    ->asArray()->one();
            if (!$detail)
                throw new \Exception('商户不存在');


            $detail['latitude_longitude'] = $detail['store']['longitude'] && $detail['store']['latitude'] ?
                $detail['store']['latitude'] . ',' . $detail['store']['longitude'] : '';
            $detail['address']          = $detail['store']['address'];
            $detail['logo']             = $detail['store']['cover_url'];
            $detail['service_mobile']   = $detail['store']['mobile'];
            $bgPicUrls                  = @json_decode($detail['store']['pic_url'], true);
            $detail['bg_pic_url']       = is_array($bgPicUrls) ? $bgPicUrls : [];
            $detail['name']             = $detail['store']['name'];
            $detail['description']      = $detail['store']['description'];
            $detail['scope']            = $detail['store']['scope'];
            $detail['district']         = [
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

            $detail['nickname'] = $detail['user']['nickname'];

            $detail['transfer_rate'] = $detail['transfer_rate'] > 0 ? (100 - $detail['transfer_rate']) / 10 : $detail['transfer_rate'];

            $detail['special_rate'] = $detail['special_rate'] > 0 ? (100 - $detail['special_rate']) / 10 : $detail['special_rate'];

            $relatEfps = EfpsMchReviewInfo::find()
                ->select("id,openAccount,paper_merchantType,paper_settleAccountType,paper_settleAccountNo,paper_settleAccount,paper_settleTarget,paper_openBank,paper_lawyerCertType,paper_lawyerCertNo,paper_certificateName,paper_openBankCode")
                ->where(["mch_id" => $this->id])
                ->one();
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
                $reviewData['openAccount'] = $reviewData['openAccount'] ? "1" : "0";
                $reviewData['paper_merchantType'] = (string)$reviewData['paper_merchantType'];
                $reviewData['paper_settleAccountType'] = (string)$reviewData['paper_settleAccountType'];
                $reviewData['paper_settleTarget'] = (string)$reviewData['paper_settleTarget'];
            }
            unset(
                $detail['category'],
                $detail['district'],
                $detail['mchAdmin'],
                $detail['store'],
                $detail['user'],
            );

            $mch_common_cat_name = MchCommonCat::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => $detail['mch_common_cat_id'],
            ])->asArray()->one();

            $detail['mch_common_cat_name'] = $mch_common_cat_name ? $mch_common_cat_name['name'] : '';

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

    public function save($data)
    {
        if (!isset($data['id']) || !$data['id'])
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请传入参数ID'
            ];

        $user_res = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_res)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户不存在',
            ];

        if (!$user_res->is_examine)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户没有审核功能,请联系客服！',
            ];

        $mch_exist = MchApply::findOne($data['id']);
        if (!$mch_exist)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '该门店不存在,请联系客服！',
            ];

        $apply_data = SerializeHelper::decode($mch_exist->json_apply_data);

        $special_rate = isset($data['special_rate']) ? $data['special_rate'] : MchApply::DEFAULT_DISCOUNT;

        $t = \Yii::$app->db->beginTransaction();
        try {
            if (isset($data['is_special_discount']) && $data['is_special_discount']) {
                if ($special_rate > 10)
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '折扣不能大于10折'
                    ];

                $mch_exist->is_special_discount             = $data['is_special_discount'];
                $apply_data['settle_discount']              = $special_rate;
                $apply_data['settle_special_rate_remark']   = $data['settle_special_rate_remark'];
                $mch_exist->json_apply_data = SerializeHelper::encode($apply_data);
                if (!$mch_exist->save())
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '保存失败'
                    ];

                $operation_save = MchApplyOperationLogSaveForm::addOperationLog($mch_exist->mall_id, $mch_exist->id,MchApplyOperationLog::OPERATION_TERMINAL_RECEPTION, \Yii::$app->user->id, MchApplyOperationLog::OPERATION_SPECIAL);
                if ($operation_save['code'] != ApiCode::CODE_SUCCESS)
                    throw new \Exception(isset($operation_save['msg']) ? $operation_save['msg'] : $operation_save['message']);


            } else {
                if ($data['status'] == MchApply::STATUS_REFUSED) { //审核不通过
                    $form = new MchApplyRefuseForm([
                        "remark" => isset($data['remark']) ? $data['remark'] : '审核不通过',
                    ]);
                } elseif($data['status'] == MchApply::STATUS_PASSED) { //审核通过
                    if ($special_rate > 9)
                        return [
                            'code'  => ApiCode::CODE_FAIL,
                            'msg'   => '折扣不能大于9折'
                        ];

                    $apply_data['settle_discount'] = $special_rate;
                    $mch_exist->json_apply_data = SerializeHelper::encode($apply_data);
                    $mch_exist->remark = '审核通过';
                    if (!$mch_exist->save())
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '保存失败'
                        ];

                    $form = new MchApplyPassForm([
                        "bind_mobile" => $mch_exist['mobile']
                    ]);
                } else {
                    throw new \Exception('审核状态错误，请联系客服！');
                }

                $form->id = $data['id'];
                $res = $form->save(MchApplyOperationLog::OPERATION_TERMINAL_RECEPTION);
                if($res['code'] != ApiCode::CODE_SUCCESS)
                    throw new \Exception(isset($res['msg']) ? $res['msg'] : $res['message']);

            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
