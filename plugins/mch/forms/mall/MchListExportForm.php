<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;

class MchListExportForm extends BaseModel{

    public $page;
    public $choose_list;
    public $is_download;
    public $review_status = null;

    private $dirPath  = "temp/mch";
    private $fileName = "商户数据";

    public function rules(){
        return [
            [['page'], 'integer', 'min' => 1],
            [['page', 'is_download', 'choose_list', 'review_status'], 'safe']
        ];
    }

    public function export(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        // 下载zip包
        if ($this->is_download) {
            return $this->download();
        }

        if ($this->page == 1) {
            $this->deleteDir($this->dirPath);
        }

        $query = Mch::find()->alias("m");
        $query->leftJoin(["s" => Store::tableName()], "s.mch_id=m.id");
        $query->leftJoin(["u" => User::tableName()], "u.mch_id=m.id");
        $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

        if(!empty($this->choose_list)){
            $query->andWhere(["IN", "m.id", $this->choose_list]);
        }

        if($this->review_status !== null){
            $query->andWhere(["m.review_status" => (int)$this->review_status]);
        }

        $query->select(["m.id as mch_id", "s.name", "s.mobile", "m.user_id",
            "u.nickname", "u.parent_id", "p.mobile as parent_mobile",
            "p.nickname as parent_nickname", "p.role_type as parent_role_type",
            "m.created_at"
        ]);

        $recordCount = $query->count();
        $isDownload  = 0;
        $fileNum     = ceil($this->page / 30); // 每300条数据保存成一个csv文件

        $list = $query->asArray()->orderBy('m.id DESC')->page($pagination, 20, $this->page)->all();
        try {
            list($dataList, $headList) = $this->transform($list);
            (new \app\core\CsvExport())->ajaxExport($dataList, $headList,
                $this->fileName . $fileNum, $this->dirPath);

            // 当查询全部数据后 返回可下载标识
            if ($pagination->current_page == $pagination->page_count) {
                $isDownload = 1;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'pagination' => $pagination,
                    'export_data' => [
                        'record_count' => (int)$recordCount,
                        'is_download'  => $isDownload,
                    ],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 数据转换
     * @param $list
     * @return array
     */
    protected function transform($list){
        $fieldsList = [
            'number'           => '序号',
            'mch_id'           => '商户ID',
            'name'             => '商户名称',
            'mobile'           => '绑定手机号',
            'user_id'          => '小程序用户ID',
            'nickname'         => '小程序用户名',
            'parent_id'        => '推荐人用户ID',
            'parent_mobile'    => '推荐人手机号',
            'parent_nickname'  => '推荐人用户名',
            'parent_role_type' => '推荐人等级',
            'created_at'       => '申请时间'
        ];
        $roleTypes = [
            'store'         => '店主',
            'partner'       => '合伙人',
            'branch_office' => '分公司',
            'user'          => '普通用户'
        ];
        $dataList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number']           = $number++;
            $arr['mch_id']           = $item['mch_id'];
            $arr['name']             = $item['name'];
            $arr['mobile']           = $item['mobile'];
            $arr['user_id']          = $item['user_id'];
            $arr['nickname']         = $item['nickname'];
            $arr['parent_id']        = $item['parent_id'];
            $arr['parent_mobile']    = $item['parent_mobile'];
            $arr['parent_nickname']  = $item['parent_nickname'];
            $arr['parent_role_type'] = isset($roleTypes[$item['parent_role_type']]) ? $roleTypes[$item['parent_role_type']] : '';
            $arr['created_at']       = date("Y-m-d H:i", $item['created_at']);
            $dataList[] = $arr;
        }
        return [$dataList, array_values($fieldsList)];
    }

    /**
     * zip下载
     * @return bool
     */
    private function download(){

        $fileList = [];
        $scanPath = \Yii::$app->basePath . \Yii::$app->urlManager->baseUrl . "/" . $this->dirPath;
        $dirs = @scandir($scanPath);
        $newDirs = [];

        foreach ($dirs as $dir) {
            if ($dir != '.' && $dir != '..') {
                $newDirs[] = $dir;
            }
        }

        for ($i = 1; $i <= count($newDirs); $i++) {
            $filePath = '/' . $this->dirPath . "/" . $this->fileName . $i . '.csv';
            $newFilePath = \Yii::$app->basePath . \Yii::$app->urlManager->baseUrl . $filePath;

            $newItem = [];
            $newItem['name'] = $this->fileName . $i . '.csv';
            $newItem['data'] = fopen($newFilePath, 'a+');
            $fileList[] = $newItem;
        }

        $this->zipFile('商户数据.zip', $fileList);

        return true;
    }

    /**
     * zip打包
     * @param $zipname
     * @param $fdList
     */
    private function zipFile($zipname, $fdList){
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

    private function deleteDir($path) {

        if (is_dir($path)) {
            //扫描一个目录内的所有目录和文件并返回数组
            $dirs = scandir($path);

            foreach ($dirs as $dir) {
                //排除目录中的当前目录(.)和上一级目录(..)
                if ($dir != '.' && $dir != '..') {
                    //如果是目录则递归子目录，继续操作
                    $sonDir = $path.'/'.$dir;
                    if (is_dir($sonDir)) {
                        //目录内的子目录和文件删除后删除空目录
                        @rmdir($sonDir);
                    } else {
                        //如果是文件直接删除
                        @unlink($sonDir);
                    }
                }
            }
            @rmdir($path);
        }
    }
}