<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台签到模板操作
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms\mall;

class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'sign_in_tpl',
                'sign_in_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/sign_in_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/sign_in_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/sign_in_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '签到提醒(类目: 服装/鞋/箱包 )',
                    'aliapp' => '打卡提醒（模板编号：AT0051 )',
                    'bdapp' => '打卡提醒（模板编号：BD0243 )',
                    'ttapp' => '打卡提醒',
                ]
            ]
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'sign_in_tpl' => [
                    'id' => '817',
                    'keyword_id_list' => [1, 3, 5],
                    'title' => '邀请成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'name1' => '',
                        'time3' => '',
                        'thing5' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'sign_in_tpl' => [
                    'id' => 'BD0243',
                    'keyword_id_list' => [14, 1, 24],
                    'title' => '打卡提醒'
                ],
                //商家活动结果通知BD1041（成功失败都用这个）
                'activity_result_tpl' => [
                    'id' => 'BD1041',
                    'keyword_id_list' => [6, 5, 8],
                    'title' => '商家活动结果通知'
                ],
            ],
        ];
    }
}
