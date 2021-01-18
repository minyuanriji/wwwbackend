<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-01
 * Time: 20:55
 */

namespace app\controllers\common;

use app\core\ApiCode;

use app\forms\common\FileUploadFrom;
use Codeception\Template\Api;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;

class UploadController extends Controller
{
    public $enableCsrfValidation = false;

    public static function allowedDomains()
    {
        return [
            // star allows all domains
            'http://localhost:8081',
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            // For cross-domain AJAX request
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to domains:
                    'Origin' => static::allowedDomains(),
                    'Access-Control-Request-Method' => ['POST'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,                 // Cache (seconds)
                ],
            ],
        ]);
    }



}