<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-22
 * Time: 15:29
 */

namespace app\forms\mall\goods;


use app\core\ApiCode;

use app\core\BasePagination;
use app\forms\mall\export\MallGoodsExport;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\ImportGoods;
use app\models\Model;
use app\models\Option;
use app\models\User;

class ImportGoodsLogForm extends BaseModel
{
    public $search;

    public function rules()
    {
        return [
            [['search'], 'string']
        ];
    }

    public function import()
    {
        $option = OptionLogic::get('import_error_log', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        if ($option) {
            $newList = $option['error_list'];
            foreach ($newList as $key => $errorItem) {
                $newList[$key]['pic_url'] = json_encode($errorItem['pic_url'], true);
                $newList[$key]['attrGroups'] = json_encode($errorItem['attrGroups'], true);
                $newList[$key]['area_limit'] = json_encode($errorItem['area_limit'], true);
                $newList[$key]['attr'] = json_encode($errorItem['attr'], true);
            }

            // 错误商品数据csv
            $export = new MallGoodsExport();
            $export->newDataList = $newList;
            $fd1 = $export->export(null);

            // 错误日志csv
            $fieldsNameList = ['序号', '商品名称', '错误信息'];
            $newDataList = [];
            foreach ($option['error_msg'] as $errorItem) {
                $newItem = [];
                $newItem['number'] = $errorItem['number'];
                $newItem['name'] = $errorItem['name'];
                $newItem['msg'] = $errorItem['msg'];
                $newDataList[] = $newItem;
            }
            $fd2 = (new \app\core\CsvExport())->exportMultiple($newDataList, $fieldsNameList);

            $fdList = [
                [
                    'name' => '商品数据.csv',
                    'data' => $fd1
                ],
                [
                    'name' => '错误日志.csv',
                    'data' => $fd2
                ],
            ];
            $this->zipFile('商品错误数据.zip', $fdList);
        }

        return true;
    }

    public function zipFile($zipname, $fdList)
    {
        $zip = new \ZipArchive();
        $zip->open($zipname, \ZipArchive::CREATE);
        foreach ($fdList as $item) {
            $zip->addFromString($item['name'], stream_get_contents($item['data']));
            fclose($item['data']);
        }
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);

    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }

        $query = ImportGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'mch_id' => \Yii::$app->admin->identity->mch_id]);
        // 日期搜索
        if (isset($search['start_date']) && $search['start_date'] && isset($search['end_date']) && $search['end_date']) {
            $query->andWhere(['>=', 'created_at', $search['start_date']]);
            $query->andWhere(['<=', 'created_at', $search['end_date']]);
        }

        if (isset($search['status']) && $search['status'] != -1) {
            // 导入状态|1.全部失败|2.部分失败|3.全部成功
            if ($search['status'] == 1) {
                $query->andWhere(['status' => [1, 2]]);
            }
            if ($search['status'] == 2) {
                $query->andWhere(['status' => [3]]);
            }
        }

        if (isset($search['user_id']) && $search['user_id'] > 0) {
            $userIds = User::find()->where(['id' => $search['user_id'], 'is_delete' => 0])
                ->select('id');
            $query->andWhere(['user_id' => $userIds]);
        }

        // 操作员列表
        $users = ImportGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->with('user')->groupBy('user_id')->all();
        $userList = [];
        $userList[] = ['user_id' => 0, 'nickname' => '全部'];
        /** @var ImportGoods $user */
        foreach ($users as $user) {
            $newItem = [];
            $newItem['user_id'] = $user->user_id;
            $newItem['nickname'] = $user->user->nickname;
            $userList[] = $newItem;
        }

        /**
         * @var BasePagination $pagination
         */
        $list = $query->with('user')->orderBy(['created_at' => SORT_DESC])->page($pagination)->all();
        $newList = [];
        /** @var ImportGoods $item */
        foreach ($list as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['file_name'] = $item->file_name;
            $newItem['created_at'] = $item->created_at;
            $newItem['goods_count'] = $item->goods_count;
            $newItem['success_count'] = $item->success_count;
            $newItem['error_count'] = $item->error_count;
            $newItem['status_cn'] = $item->getStatusText($item);
            $newItem['nickname'] = $item->user->nickname;
            $newList[] = $newItem;
        }

        $option = OptionLogic::get(Option::NAME_IMPORT_ERROR_LOG, \Yii::$app->mall->id, Option::GROUP_ADMIN);
        $isDownload = false;
        if ($option && count($option['error_list']) > 0) {
            $isDownload = true;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'user_list' => $userList,
                'is_download' => $isDownload,
                'pagination' => $pagination,
            ]
        ];
    }
}