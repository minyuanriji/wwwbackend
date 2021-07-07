<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 17:04
 */

namespace app\plugins\diy\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\behaviors\LoginFilter;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\helpers\SerializeHelper;
use app\plugins\diy\forms\api\DivPageDetailForm;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\forms\api\InfoForm;
use app\models\Goods;
use app\forms\common\goods\GoodsMember;

class PageController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => \app\controllers\behavior\LoginFilter::class,
                'safeActions' => ['detail']
            ]
        ]);
    }

    public function actionDetail($id)
    {
        $form = new DivPageDetailForm([
            "id" => $id
        ]);
        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    public function actionStore()
    {
        $form = new InfoForm();
        $form->form_data = json_decode(\Yii::$app->request->post('form_data'), true);
        return $this->asJson($form->save());
    }
}
