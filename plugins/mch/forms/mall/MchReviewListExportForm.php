<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchCommonCat;

class MchReviewListExportForm extends BaseModel{

    public $page;
    public $choose_list;
    public $is_download;
    public $review_status = null;

    private $dirPath  = "temp/mch";
    private $fileName = "商户入驻申请数据";

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

        $query = MchApply::find()->alias("ma");
        $query->innerJoin(["u" => User::tableName()], "u.id=ma.user_id");
        $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

        if(!empty($this->choose_list)){
            $query->andWhere(["IN", "ma.id", $this->choose_list]);
        }

        if($this->review_status == "special_discount"){ //特殊折扣申请
            $query->andWhere(["ma.is_special_discount" => 1]);
        }else{
            $query->andWhere(["ma.status" => $this->review_status]);
        }

        $selects = ["ma.*", "p.role_type as parent_role_type", "p.nickname as parent_nickname"];
        $selects[] = "p.id as parent_id";
        $selects[] = "p.mobile as parent_mobile";
        $selects[] = "u.nickname";

        $query->select($selects);

        $recordCount = $query->count();
        $isDownload  = 0;
        $fileNum     = ceil($this->page / 30); // 每300条数据保存成一个csv文件

        $list = $query->asArray()->orderBy('ma.id DESC')->page($pagination, 20, $this->page)->all();
        if($list){
            $statusText = [
                'refused'   => '拒绝',
                'passed'    => '已通过',
                'verifying' => '审核中',
                'applying'  => '资料填写中'
            ];
            $roleTypeText = [
                'store'         => 'VIP代理商',
                'partner'       => '合伙人',
                'branch_office' => '分公司',
                'user'          => 'VIP会员'
            ];
            foreach($list as &$row){
                $applyData = !empty($row['json_apply_data']) ? json_decode($row['json_apply_data'], true) : [];
                $row = array_merge($row, $applyData);
                $row['bind_mobile'] = !empty($row['bind_mobile']) ? $row['bind_mobile'] : $row['mobile'];
                $city = CityHelper::reverseData($row['store_district_id'], $row['store_city_id'], $row['store_province_id']);
                $row['province'] = !empty($city['province']) ? $city['province']['name'] : "";
                $row['city'] = !empty($city['city']) ? $city['city']['name'] : "";
                $row['district'] = !empty($city['district']) ? $city['district']['name'] : "";

                $row['status_text'] = isset($statusText[$row['status']]) ? $statusText[$row['status']] : '';

                $row['apply_date'] = date("Y-m-d H:i:s", $row['created_at']);

                $row['parent_role_text'] = isset($roleTypeText[$row['parent_role_type']]) ? $roleTypeText[$row['parent_role_type']] : '';

                unset($row['json_apply_data']);
            }
        }

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
            'user_id'          => '用户ID',
            'nickname'         => '用户名',
            'realname'         => '申请人',
            'mobile'           => '手机号',
            'status_text'      => '状态',
            'apply_date'       => '申请日期',
            'parent_id'        => '推荐人用户ID',
            'parent_mobile'    => '推荐人手机号',
            'parent_nickname'  => '推荐人用户名',
            'parent_role_text' => '推荐人等级',
            'store_name'       => '店铺名称',
            'province'         => '省份',
            'city'             => '城市',
            'district'         => '地区',
            'store_address'    => '详细地址'
        ];
        $dataList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            foreach($fieldsList as $key => $text){
                if($key == "number"){
                    $arr['number'] = $number++;
                }else{
                    $arr[$key] = isset($item[$key]) ? $item[$key] : "";
                }
            }
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