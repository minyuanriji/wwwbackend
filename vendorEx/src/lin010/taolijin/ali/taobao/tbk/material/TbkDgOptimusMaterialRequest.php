<?php


namespace lin010\taolijin\ali\taobao\tbk\material;



use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

/**
 * 淘宝客-推广者-物料精选
 * @property string $page_size 页大小，默认20，1~100
 * @property string $page_no 第几页，默认：1
 * @property string $adzone_id mm_xxx_xxx_xxx的第三位
 * @property string $material_id 官方的物料Id(详细物料id见：https://market.m.taobao.com/app/qn/toutiao-new/index-pc.html#/detail/10628875?_k=gpov9a)
 * @property string $device_value 智能匹配-设备号加密后的值（MD5加密需32位小写），类型为OAID时传原始OAID值
 * @property string $device_encrypt 智能匹配-设备号加密类型：MD5，类型为OAID时不传
 * @property string $device_type 智能匹配-设备号类型：IMEI，或者IDFA，或者UTDID（UTDID不支持MD5加密），或者OAID
 * @property string $content_id 内容专用-内容详情ID
 * @property string $content_source 内容专用-内容渠道信息
 * @property string $item_id 商品ID，用于相似商品推荐
 * @property string $favorites_id 选品库投放id
 */
class TbkDgOptimusMaterialRequest extends TbkBaseRequest {

    public function getApiMethodName(){
        return "taobao.tbk.dg.optimus.material";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check(){
        // TODO: Implement check() method.
    }
}