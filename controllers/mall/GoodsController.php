<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-10
 * Time: 20:19
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\common\goods\PluginMchGoods;
use app\forms\common\mch\MchMallSettingForm;
use app\forms\mall\goods\CopyForm;
use app\forms\mall\goods\GoodsEditForm;
use app\forms\mall\goods\GoodsForm;
use app\forms\mall\goods\GoodsListForm;
use app\forms\mall\goods\ImportGoodsForm;
use app\forms\mall\goods\ImportGoodsLogForm;
use app\forms\mall\goods\LabelEditForm;
use app\forms\mall\goods\LabelForm;
use app\forms\mall\goods\LabelListForm;
use app\forms\mall\goods\RecommendSettingForm;
use app\forms\mall\goods\TaobaoCsvForm;
use app\forms\mall\goods\TransferForm;
use app\models\Label;
use app\controllers\business\ExportData;
class GoodsController extends MallController
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 9:58
     * @Note:商品列表
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new GoodsListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->getList();
                return $this->asJson($res);
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new GoodsListForm();
                $form->flag = \Yii::$app->request->post('flag');
                $chooseList = \Yii::$app->request->post('choose_list');
                $form->choose_list = $chooseList ? explode(',', $chooseList) : [];
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();

                $form = new GoodsEditForm();

                $data_form = json_decode($data['form'], true);

                //调试,可删
//                $data_form['max_deduct_integral'] = 29;
//                $data_form['enable_integral']     = 1;
//                $data_form['integral_setting']    = array(
//                    'integral_num' => '300',
//                    'period'       => '1',
//                    'period_unit'  => 'month',
//                    'expire'       => '90',
//                );
//                $data_form['price_display']       = [
//                    ['key' => 'price', "display_id" => 1],
//                ];
                //调试,可删

                $form->attributes = $data_form;

                $form->attrGroups = json_decode($data['attrGroups'], true);

                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:商品标签
     */
    public function actionLabel()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new LabelListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->search();
                return $this->asJson($res);
            }
        }

        return $this->render('label');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:标签编辑
     */
    public function actionLabelEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LabelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new LabelForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->search();
                return $this->asJson($res);
            }
        }

        return $this->render('label-edit');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:商品标签删除
     */
    public function actionLabelDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $label = Label::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
                if (!$label) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该数据不存在或已被删除！']);
                }
                $label->is_delete = 1;
                if ($label->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 9:59
     * @Note:商品删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 9:59
     * @Note:上下架
     * @return \yii\web\Response
     */
    public function actionSwitchStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 9:59
     * @Note:快速购买
     * @return \yii\web\Response
     */
    public function actionSwitchQuickShop()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchQuickShop();

        return $this->asJson($res);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:00
     * @Note:上商户商品申请上架
     * @return \yii\web\Response
     */

    public function actionApplyStatus()
    {
        $form = new PluginMchGoods();
        $form->goods_id = \Yii::$app->request->post('id');
        $form->mch_id = \Yii::$app->admin->identity->mch_id;

        return $this->asJson($form->applyStatus());
    }

// 商品采集
    public function actionCollect()
    {
        $form = new CopyForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionTaobaoCsv()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TaobaoCsvForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('taobao-csv');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:02
     * @Note:排序
     * @return \yii\web\Response
     */
    public function actionEditSort()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editSort();
        return $this->asJson($res);
    }

    public function actionTransfer()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TransferForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->transfer());
            }
        } else {
            return $this->render('transfer');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:02
     * @Note:批量删除
     * @return \yii\web\Response
     */
    public function actionBatchDestroy()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchDestroy();
        return $this->asJson($res);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-17
     * @Time: 16:41
     * @Note:批量更新状态
     * @return \yii\web\Response
     */
    public function actionBatchUpdateStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:02
     * @Note:商品面议
     * @return \yii\web\Response
     */
    public function actionBatchUpdateNegotiable()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateNegotiable();

        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:03
     * @Note:批量设置运费
     * @return \yii\web\Response
     */
    public function actionBatchUpdateFreight()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateFreight();

        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:03
     * @Note:批量限购
     * @return \yii\web\Response
     */
    public
    function actionBatchUpdateConfineCount()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateConfineCount();

        return $this->asJson($res);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:03
     * @Note:批量设置积分
     * @return \yii\web\Response
     */
    public function actionBatchUpdateIntegral()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateIntegral();
        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:04
     * @Note:推荐设置
     * @return string|\yii\web\Response
     */
    public function actionRecommendSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RecommendSettingForm();
                $form->data = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new RecommendSettingForm();
                $form->attributes = \Yii::$app->request->post();

                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('recommend-setting');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-25
     * @Time: 10:04
     * @Note:推荐商品
     * @return \yii\web\Response
     */
    public function actionRecommendGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getRecommendGoodsList();
        return $this->asJson($res);
    }

// 更新商品名称
    public function actionUpdateGoodsName()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updateGoodsName();

        return $this->asJson($res);
    }

// TODO 移至 IndexController 此处即将废弃
    public function actionPermissions()
    {
        $permissions = \Yii::$app->role->getAccountPermission();
        if (\Yii::$app->admin->identity->mch_id) {
            /** @var MchMallSetting $setting */
            $permissions = [];
            $setting = (new MchMallSettingForm())->search();
            if ($setting->is_distribution) {
                $permissions[] = 'share';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ];
    }

    public function actionBatchUpdateGoodsPrice()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateGoodsPrice();

        return $this->asJson($res);
    }

    public function actionImportGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ImportGoodsForm();
            $form->attributes = \Yii::$app->request->post();
            $res = $form->save();

            return $this->asJson($res);
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new ImportGoodsLogForm();
                $form->import();
                return false;
            } else {
                return $this->render('import-goods');
            }
        }
    }

    public function actionImportGoodsLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ImportGoodsLogForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new ImportGoodsLogForm();
                $form->import();
                return false;
            } else {
                return $this->render('import-goods-log');
            }

        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 15:07
     * @Note:导出excel
     * @return \yii\web\Response
     */
    public function actionExportGoodsList()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new GoodsListForm();
            $form->flag = \Yii::$app->request->post('flag');
            $form->choose_list = \Yii::$app->request->post('choose_list');
            $res = $form->getList();
            return $this->asJson($res);
        }
    }

    public function actionGetGoodsData(){
        $goods_id = \Yii::$app->request->get('choose_list');
        $goods_id = !empty($goods_id) ? $goods_id : '*';
        $res = (new ExportData()) -> getGoodsData($goods_id);
    }


}