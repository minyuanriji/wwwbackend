<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 15:36
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\common\AttachmentUploadForm;
use app\forms\mall\wechat\MaterialArticleEditForm;
use app\forms\mall\wechat\MaterialArticleListForm;
use app\forms\mall\wechat\MaterialEditForm;
use app\forms\mall\wechat\MaterialListForm;
use app\forms\mall\wechat\MaterialUploadForm;
use app\forms\mall\wechat\ReplyRuleEditForm;
use app\forms\mall\wechat\ReplyRuleListForm;
use app\forms\mall\wechat\WechatEditForm;
use app\forms\mall\wechat\WechatForm;
use app\models\BaseModel;
use app\models\DistrictData;
use app\models\Material;
use app\models\MaterialArticle;
use app\models\ReplyRule;
use app\models\RuleKeyword;
use app\models\Wechat;
use app\plugins\Controller;
use EasyWeChat\Kernel\Exceptions\HttpException;
use yii\db\Exception;
use yii\web\UploadedFile;

class WechatController extends MallController
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-20
     * @Time: 15:37
     * @Note: 设置
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new WechatEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new WechatForm();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 18:07
     * @Note:自定义菜单
     * @return string|\yii\web\Response
     */
    public function actionMenus()
    {
        /** @var \jianyan\easywechat\Wechat $wechat */
        $wechat = \Yii::$app->wechat;
        $app = $wechat->app;
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $buttons = \Yii::$app->request->post('form');
                $res = $app->menu->create($buttons['button']);
                if ($res['errcode'] == 0) {
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '发布成功']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => "错误码:{$res['errmsg']}"]);
            } else {
                try {
                    $list = $app->menu->list();
                } catch (HttpException $e) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => "获取菜单失败，errcode：{$e->formattedResponse['errcode']},errmsg：{$e->formattedResponse['errmsg']}"]);
                }

                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '获取成功', 'data' => $list]);
            }
        }
        return $this->render('menus');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:32
     * @Note:自动回复
     * @return string|\yii\web\Response
     */
    public function actionReply()
    {


        $wechat = \Yii::$app->wechat->app;
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $buttons = \Yii::$app->request->post('form');
                $res = $wechat->menu->create($buttons['button']);
                if ($res['errcode'] == 0) {
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '发布成功']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => "错误码:{$res['errmsg']}"]);
            } else {
                try {
                    $list = $wechat->menu->list();
                } catch (HttpException $e) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => "获取菜单失败，errcode：{$e->formattedResponse['errcode']},errmsg：{$e->formattedResponse['errmsg']}"]);
                }

                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '获取成功', 'data' => $list]);
            }
        }
        return $this->render('reply');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:31
     * @Note:素材管理
     * @return string|\yii\web\Response
     */
    public function actionMaterial()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $model = new MaterialEditForm();
                $model->attributes = $form;
                return $this->asJson($model->save());
            } else {
                $form = \Yii::$app->request->get();
                $model = new MaterialListForm();
                $model->attributes = $form;
                return $this->asJson($model->search());
            }
        }
        return $this->render('material');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 14:36
     * @Note:素材删除
     * @return \yii\web\Response
     */

    public function actionMaterialDelete()
    {
        $app = \Yii::$app->wechat->getApp();
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $material = Material::findOne(['is_delete' => 0, 'id' => $form['id']]);
                if (!$material) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '所选内容不存在或已被删除！']);
                }
                $material->is_delete = 1;
                if ($material->save()) {
                    $app->material->delete($material->media_id);
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败']);
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:08
     * @Note:图文素材
     * @return string|\yii\web\Response
     */

    public function actionArticle()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $model = new MaterialArticleEditForm();
                $model->attributes = $form;
                return $this->asJson($model->save());
            } else {
                $form = \Yii::$app->request->get('form');
                $model = new MaterialArticleListForm();
                $model->attributes = $form;
                return $this->asJson($model->search());
            }
        }
        return $this->render('article');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:31
     * @Note:删除图文
     * @return \yii\web\Response
     */

    public function actionArticleDelete()
    {

        $app = \Yii::$app->wechat->getApp();
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $article = MaterialArticle::findOne(['is_delete' => 0, 'id' => $form['id']]);
                if (!$article) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '所选内容不存在或已被删除！']);
                }
                $article->is_delete = 1;
                if ($article->save()) {
                    $app->material->delete($article->media_id);
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败']);
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:31
     * @Note:上传素材 非文章
     */
    public function actionMaterialUpload()
    {
        $form = new MaterialUploadForm();
        $form->file = UploadedFile::getInstanceByName('file');
        $this->asJson($form->save());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 13:31
     * @Note:上传封面
     * @return \yii\web\Response
     */
    public function actionUploadCover()
    {
        $form = new MaterialUploadForm();
        $form->file = UploadedFile::getInstanceByName('file');
        $res = $form->save();
        if ($res['code'] == 1) {
            return $this->asJson($res);
        }
        $app = \Yii::$app->wechat->getApp();
        $result = $app->material->uploadImage(ROOT_PATH . $res['data']['url']);
        if ($result) {
            //存入数据库
            $data['media_id'] = $result['media_id'];
            // $data['media_id'] = 'I-5BQ5JLvNSaIE-U4RvcsFeWRGG3CT3pORidQl1cc1w';
            $data['thumb_url'] = $res['data']['thumb_url'];
        }
        $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'success', 'data' => $data]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 15:48
     * @Note:关键词匹配
     * @return string|\yii\web\Response
     */
    public function actionReplyRule()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $model = new ReplyRuleEditForm();
                $model->attributes = $form;
                return $this->asJson($model->save());
            } else {
                $form = \Yii::$app->request->get();
                $model = new ReplyRuleListForm();
                $model->attributes = $form;
                return $this->asJson($model->search());
            }
        }
        return $this->render('reply-rule');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-23
     * @Time: 20:36
     * @Note:删除自动回复
     * @return \yii\web\Response
     */
    public function actionReplyDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post('form');
                $rule = ReplyRule::findOne(['is_delete' => 0, 'id' => $form['id']]);
                if (!$rule) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '所选内容不存在或已被删除！']);
                }
                $rule->is_delete = 1;
                if ($rule->save()) {
                    RuleKeyword::updateAll(['is_delete' => 1], ['rule_id' => $rule->id]);
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败']);
            }
        }
    }


}