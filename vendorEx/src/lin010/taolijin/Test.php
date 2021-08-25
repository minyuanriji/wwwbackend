<?php
namespace lin010\taolijin;

class Test
{
    public static function run(){
        include_once __DIR__ . '/ali_sdk/TopSdk.php';

        $c = new \TopClient();
        $c->appkey    = "33062416";
        $c->secretKey = "a5de1941f9aa0c70101d110128ce0729";

        //$req = new TbkDgOptimusMaterialRequest();
        //$req->page_size   = "20"; //页大小，默认20，1~100
        //$req->page_no     = "1"; //	第几页，默认：1
        //$req->adzone_id   = "111611450447"; //mm_xxx_xxx_xxx的第三位
        //$req->material_id = "13366"; //官方的物料Id(详细物料id见：https://market.m.taobao.com/app/qn/toutiao-new/index-pc.html#/detail/10628875?_k=gpov9a)

        /*
        $req->device_value = "xxx"; //智能匹配-设备号加密后的值（MD5加密需32位小写），类型为OAID时传原始OAID值
        $req->device_encrypt = "MD5"; //智能匹配-设备号加密类型：MD5，类型为OAID时不传
        $req->device_type = "IMEI"; //智能匹配-设备号类型：IMEI，或者IDFA，或者UTDID（UTDID不支持MD5加密），或者OAID
        $req->content_id = "323"; //内容专用-内容详情ID
        $req->content_source = "xxx"; //内容专用-内容渠道信息
        $req->item_id = "33243"; //商品ID，用于相似商品推荐
        $req->favorites_id = "123445"; //选品库投放id
        */

        /*$req = new TbkDgMaterialOptionalRequest();
        $req->cat           = "16,18"; //商品筛选-后台类目ID。用,分割，最大10个，该ID可以通过taobao.itemcats.get接口获取到
        $req->q             = "咖啡"; //商品筛选-查询词
        $req->adzone_id     = "111611450447"; //mm_xxx_xxx_12345678三段式的最后一段数字
        $req->start_dsr     = "10"; //商品筛选(特定媒体支持)-店铺dsr评分。筛选大于等于当前设置的店铺dsr评分的商品0-50000之间
        $req->page_size     = "20"; //页大小，默认20，1~100
        $req->page_no       = "1"; //第几页，默认：１
        $req->platform      = "1"; //链接形式：1：PC，2：无线，默认：１
        $req->end_tk_rate   = "1234"; //商品筛选-淘客佣金比率上限。如：1234表示12.34%
        $req->start_tk_rate = "1234"; //商品筛选-淘客佣金比率下限。如：1234表示12.34%
        $req->end_price     = "10"; //商品筛选-折扣价范围上限。单位：元
        $req->start_price   = "10"; //商品筛选-折扣价范围下限。单位：元
        $req->is_overseas   = "false"; //商品筛选-是否海外商品。true表示属于海外商品，false或不设置表示不限
        $req->is_tmall      = "false"; //商品筛选-是否天猫商品。true表示属于天猫商品，false或不设置表示不限
        $req->sort          = "tk_rate_des"; //排序_des（降序），排序_asc（升序），销量（total_sales），淘客佣金比率（tk_rate）， 累计推广量（tk_total_sales），总支出佣金（tk_total_commi），价格（price），匹配分（match）
        $req->itemloc       = "杭州"; //商品筛选-所在地
        $req->material_id   = "2836"; //不传时默认物料id=2836；如果直接对消费者投放，可使用官方个性化算法优化的搜索物料id=17004
        $req->has_coupon = "false"; //优惠券筛选-是否有优惠券。true表示该商品有优惠券，false或不设置表示不限
        $req->ip = "13.2.33.4"; //	ip参数影响邮费获取，如果不传或者传入不准确，邮费无法精准提供
        $req->need_free_shipment = "true"; //商品筛选-是否包邮。true表示包邮，false或不设置表示不限
        $req->need_prepay = "true"; //商品筛选-是否加入消费者保障。true表示加入，false或不设置表示不限
        $req->include_pay_rate_30 = "true"; //商品筛选(特定媒体支持)-成交转化是否高于行业均值。True表示大于等于，false或不设置表示不限
        $req->include_good_rate = "true"; //商品筛选-好评率是否高于行业均值。True表示大于等于，false或不设置表示不限
        $req->include_rfd_rate = "true"; //商品筛选(特定媒体支持)-退款率是否低于行业均值。True表示大于等于，false或不设置表示不限
        $req->npx_level = "2"; //商品筛选-牛皮癣程度。取值：1不限，2无，3轻微
        $req->end_ka_tk_rate = "1234"; //商品筛选-KA媒体淘客佣金比率上限。如：1234表示12.34%
        $req->start_ka_tk_rate = "1234"; //商品筛选-KA媒体淘客佣金比率下限。如：1234表示12.34%
        $req->device_encrypt = "MD5"; //智能匹配-设备号加密类型：MD5
        $req->device_value = "xxx"; //智能匹配-设备号加密后的值（MD5加密需32位小写）
        $req->device_type = "IMEI"; //智能匹配-设备号类型：IMEI，或者IDFA，或者UTDID（UTDID不支持MD5加密），或者OAID
        $req->lock_rate_end_time = "1567440000000"; //锁佣结束时间
        $req->lock_rate_start_time = "1567440000000"; //锁佣开始时间
        $req->longitude = "121.473701"; //本地化业务入参-LBS信息-经度
        $req->latitude = "31.230370"; //本地化业务入参-LBS信息-纬度
        $req->city_code = "310000"; //本地化业务入参-LBS信息-国标城市码，仅支持单个请求，请求饿了么卡券物料时，该字段必填。 （详细城市ID见：https://mo.m.taobao.com/page_2020010315120200508）
        $req->seller_ids = "1,2,3,4"; //商家id，仅支持饿了么卡券商家ID，支持批量请求1-100以内，多个商家ID使用英文逗号分隔
        $req->special_id = "2323"; //会员运营ID
        $req->relation_id = "3243"; //渠道关系ID，仅适用于渠道推广场景
        $req->page_result_key = "abcdef"; //本地化业务入参-分页唯一标识，非首页的请求必传，值为上一页返回结果中的page_result_key字段值
        $req->ucrowd_id = "1"; //人群ID，仅适用于物料评估场景material_id=41377
        $req->ucrowd_rank_items = json_encode([
            "commirate" => "1234",
            "price"     => "10.12",
            "item_id"   => "542808901898"
        ]);
        $req->get_topn_rate = "0";*/

        $resp = $c->execute($req);
        print_r($resp);
        exit;
    }
}