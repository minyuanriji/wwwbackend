<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

namespace app\forms\mall\export;

use app\models\BaseModel;
use app\models\PaymentOrder;

abstract class BaseExport extends BaseModel
{
    public $fieldsKeyList;
    public $fieldsNameList;
    public $dataList;

    abstract public function fieldsList();

    abstract public function export($query);

    abstract protected function transform($list);

    protected function getFields()
    {
        $fieldsList = $this->fieldsList();
        $newFields = ['序号'];
        if ($this->fieldsKeyList) {
            foreach ($this->fieldsKeyList as $field) {
                foreach ($fieldsList as $item) {
                    if ($item['key'] === $field) {
                        $newFields[] = $item['value'];
                    }
                }
            }
        }
        $this->fieldsKeyList = array_merge(['number'], $this->fieldsKeyList ?: []);
        $this->fieldsNameList = $newFields;
    }

    protected function getDataList()
    {
        $newData = [];
        foreach ($this->dataList as $key => $item) {
            $arr = [];
            foreach ($this->fieldsKeyList as $fieldsKey) {
                if (isset($item[$fieldsKey])) {
                    $arr[] = $item[$fieldsKey];
                } else {
                    $arr[] = '';
                }
            }
            $newData[] = $arr;
        }
        return $newData;
    }

    protected function getPlatform($platform)
    {
        switch ($platform) {
            case 'wxapp':
                $value = '微信';
                break;
            case 'aliapp':
                $value = '支付宝';
                break;
            case 'ttapp':
                $value = '头条';
                break;
            case 'bdapp':
                $value = '百度';
                break;
            default:
                $value = '未知';
                break;
        }

        return $value;
    }

    protected function getPayPlatform($payType)
    {
        switch ($payType) {
            case PaymentOrder::PAY_TYPE_WECHAT:
                $value = '微信';
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $value = 'H5';
                break;
            default:
                $value = '未知';
                break;
        }

        return $value;
    }

    protected function getDateTime($dateTime)
    {
        return (int)$dateTime > 0 ? date("Y-m-d H:i:s",$dateTime) : '';
    }
}
