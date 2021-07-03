<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导出核心类
 * Author: zal
 * Date: 2020-04-16
 * Time: 21:32
 */

namespace app\forms\mall\export;

class CsvExport extends \app\core\CsvExport
{
    public function handleRowData($data)
    {
        try {
            $row = $data;
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $newValText = '';
                    foreach ($value as $item) {
                        if (isset($item['value']) && is_string($item['value'])) {
                            $valText = $item['label'] . '：' . $item['value'];
                        } else {
                            $valText = $item['label'] . '：';
                        }
                        if ($item['key'] == 'radio') {
                            if (isset($item['value']) && $item['value']) {
                                $valText = $item['label'] . "：" . $item['value'];
                            } else {
                                $valText = $item['label'] . "：";
                            }
                        }
                        if ($item['key'] == 'checkbox') {
                            $valText = '';
                            if (isset($item['value'])) {
                                if (is_array($item['value'])) {
                                    foreach ($item['value'] as $valItem) {
                                        $valText .= $valItem . '|';
                                    }
                                    $valText = substr($valText, 0, strlen($valText) - 1);
                                } else {
                                    $valText = $item['value'];
                                }
                                $valText = $item['label'] . '：' . $valText;
                            } else {
                                $valText = $item['label'] . '：';
                            }
                        }
                        if (isset($item['key']) && $item['key'] == 'img_upload') {
                            $valText = '';
                            if (isset($item['value'])) {
                                if (is_array($item['value'])) {
                                    foreach ($item['value'] as $valItem) {
                                        $valText .= $valItem . '|';
                                    }
                                    $valText = substr($valText, 0, strlen($valText) - 1);
                                } else {
                                    $valText = $item['value'];
                                }
                                $valText = $item['label'] . '：' . $valText;
                            } else {
                                $valText = $item['label'] . '：';
                            }
                        }

                        if ($valText) {
                            $newValText = $newValText . ',' . $valText;
                        }
                        if ($newValText) {
                            $newValText = ltrim($newValText, ',');
                        }
                    }


                    $newValText = $this->check($newValText);
                    $row[$key] = mb_convert_encoding($newValText, 'GBK', 'UTF-8');
                } else {
                    $newValue = $this->check($value);
                    $row[$key] = mb_convert_encoding($newValue, 'GBK', 'UTF-8');
                }
            }

            $data = array_values($row);
            return $data;
        } catch (\Exception $e) {
            dd($e->getMessage() . '-' . $e->getLine());
        }
    }
}