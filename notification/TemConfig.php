<?php

namespace app\notification;

class TemConfig
{
    /*  @todo 提现到帐通知

        您申请的提现金额已到帐.
        申请时间：2015/05/25 14:58
        提现方式：银行卡转帐
        提现金额：100.00
        手续费用：2.00
        到账金额：98.00
        感谢你的使用.

        {{first.DATA}}
        申请时间：{{keyword1.DATA}}
        提现方式：{{keyword2.DATA}}
        提现金额：{{keyword3.DATA}}
        手续费用：{{keyword4.DATA}}
        到账金额：{{keyword5.DATA}}
        {{remark.DATA}}
     * */
    const NoticeOfWithdrawal = '0d_ck3gQZprV4A4KEONI8YSoZJ4jDT9Nse0nUnSA_UU';

    /*  @todo 退款通知

        您好，您对微信影城影票的抢购未成功，已退款。

        退款原因：未抢购成功
        退款金额：70元
        备注：如有疑问，请致电13912345678联系我们，或回复M来了解详情。

        {{first.DATA}}

        退款原因：{{reason.DATA}}
        退款金额：{{refund.DATA}}
        {{remark.DATA}}

     * */
    const Refund = '2qQH4QQUr9K7pMISYmFZcPaBqJOUw0WbtT8dnMT1UvA';

    /*  @todo 支付成功通知

        您好，您的微信支付已成功
        订单编号：123456789901234567
        消费金额：28.88元
        消费门店：一元超市
        消费时间：2016年4月8日17:00:00
        点击累计消费金额

        {{first.DATA}}
        订单编号：{{keyword1.DATA}}
        消费金额：{{keyword2.DATA}}
        消费门店：{{keyword3.DATA}}
        消费时间：{{keyword4.DATA}}
        {{remark.DATA}}

     * */
    const OrderPaidSuccessfully = '4OTpgKhJe6igdWpW98aLLdTav9LQttEFZdDRRgDasbY';

    /*  @todo 提现申请通知

        你好,提现申请已经收到
        昵称：张三
        时间：2016年5月13日
        金额：10.00元
        提现方式：微信
        感谢你的使用。

        {{first.DATA}}
        昵称：{{keyword1.DATA}}
        时间：{{keyword2.DATA}}
        金额：{{keyword3.DATA}}
        方式：{{keyword4.DATA}}
        {{remark.DATA}}

     * */
    const NoticeWithdrawalApplication = 'Dvl1-pZeOoR8sqhRLDBCYPodWQYosqng7aTUbK1TGNo';

    /*  @todo 新订单通知

        您收到了一条新的订单。

        提交时间：02月18日 01时05分
        订单类型：询价订单
        客户信息：广州 王俊
        兴趣车型：骐达 2011款 1.6 CVT 舒适版

        截止24日09:39分,您尚有10个订单未处理。

        {{first.DATA}}

        提交时间：{{tradeDateTime.DATA}}
        订单类型：{{orderType.DATA}}
        客户信息：{{customerInfo.DATA}}
        {{orderItemName.DATA}}：{{orderItemData.DATA}}
        {{remark.DATA}}

     * */
    const NewOrder = 'HjruKiCf7wgKwxf1ndS5A3PUrGYS0_L33urKAuGiQ-A';

    /*  @todo 订单包裹跟踪通知

        您好，您的订单包裹已被您拒收

        订单号：C0-xxxxxxx-xxxxxxx
        包裹单号：xxxxxxxxxxxxxx
        付款信息：￥125.67 现金付款
        配送信息：1860000000
        收件人：邹先生
        如有疑问，请联系我们

        {{first.DATA}}

        订单号：{{order_id.DATA}}
        包裹单号：{{package_id.DATA}}
        {{remark.DATA}}
     * */
    const OrderTrackingNotification = 'JD341uE5o0oS9QfaRkUPXEbjiaFKWCYvtcOkZpANHVE';

    /*  @todo 店铺审核通知

        店铺审核已通过
        店铺名：红旗飘飘
        账号名：123456
        审核时间：2017-09-01 14:12:10
        如有疑问请联系021-88888888

        {{first.DATA}}
        店铺名：{{keyword1.DATA}}
        账号名：{{keyword2.DATA}}
        审核时间：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const ShopApproved = 'P7xEjRG_Mmo-daLn2WVT7VBS8KXEJ1p3Np7nu26v_IQ';

    /*  @todo 退货申请不通过通知

        退货申请不通过通知提醒
        带机单号：666666
        卖场：文昌店
        导购员：小李
        点击查看详情

        {{first.DATA}}
        带机单号：{{keyword1.DATA}}
        卖场：{{keyword2.DATA}}
        导购员：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const NoticeRejectionReturnApplication = 'YVvKWlVCsI0wsj623kkaStTG4ujKzuXcx_3D6SOwNV8';

    /*  @todo 商品已发出通知

        亲，宝贝已经启程了，好想快点来到你身边

        快递公司：顺丰快递
        快递单号：3291987391
        备注：如果疑问，请在微信服务号中输入“KF”，**将在第一时间为您服务！

        {{first.DATA}}

        快递公司：{{delivername.DATA}}
        快递单号：{{ordername.DATA}}
        {{remark.DATA}}

     * */
    const GoodsHaveBeenSent = 'ZO3iGIKPOdedWbNS_WOSAQn3efN3mXy230GsSaZnUFk';

    /*  @todo 消费成功通知

        您的推荐客户购买商品
        消费金额：100
        订单商品：测试商品
        验证商户：测试商户
        获得奖励：100.00
        当前奖励：150.00
        感谢您的使用

        {{first.DATA}}
        消费金额：{{keyword1.DATA}}
        订单商品：{{keyword2.DATA}}
        验证商户：{{keyword3.DATA}}
        获得奖励：{{keyword4.DATA}}
        当前奖励：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const SuccessfulConsumption = 'axc6kwJVZn_WBpHgkJ9Uif2W4NQFBkS-UKNlik-d1HQ';

    /*  @todo 提现申请成功通知

        恭喜，您的提现已转银行处理，具体到账时间以银行为准，请注意查收~~
        提现金额：￥1000.00
        处理时间：2016-08-27 20:20:58
        提现方式：中国农业银行(33**333)
        感谢您的使用！如有疑问请联系客服

        {{first.DATA}}
        提现金额：{{keyword1.DATA}}
        处理时间：{{keyword2.DATA}}
        提现方式：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const SuccessfulWithdrawalApplication = 'd3njw4-phYkVqQwcpNYxPMZ6NHOKgnj0cmqdzPaEO_M';

    /*  @todo 店铺审核通知

        尊敬的张三，您有新的店铺信息需要审核
        店铺编号：ABC001
        店铺名称：连锁店ABC
        店铺店主：李四
        手机号码：13800138000
        提交时间：2016年3月4号 15:00
        请尽快为客户进行店铺审核，谢谢。

        {{first.DATA}}
        店铺编号：{{keyword1.DATA}}
        店铺名称：{{keyword2.DATA}}
        店铺店主：{{keyword3.DATA}}
        手机号码：{{keyword4.DATA}}
        提交时间：{{keyword5.DATA}}
        {{remark.DATA}}
     * */
    const ShopAudit = 'hElK22eezgqvmWPtyhy62JEpQ0xj6QkDlhzV1ZDecyg';

    /*  @todo 店铺审核通知

        您的店铺已通过审核
        店铺名称：欢乐便利店
        审核结果：审核通过
        处理时间：2016年6月18日18:00
        请前往您的店铺进行更多设置

        {{first.DATA}}
        店铺名称：{{keyword1.DATA}}
        审核结果：{{keyword2.DATA}}
        处理时间：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const StorePassedAudit = 'hhs7TFHl-xcYstR79gp4LO0qwnES-vn3yjWHF7_ZceQ';

    /*  @todo 售后服务处理进度提醒

        您好，您的售后单2905928有新的客服回复：

        服务类型：换货
        处理状态：待处理
        提交时间：2013-12-23 14:48:24
        当前进度：您好，订单是第三方物流配送，无法直接取消，已登物流人员拦截，若拦截不成功，您请直接拒收哦，货款已有客服…

        点击“详情”查看详细处理结果，如有疑问可回复KF联系

        {{first.DATA}}

        服务类型：{{HandleType.DATA}}
        处理状态：{{Status.DATA}}
        提交时间：{{RowCreateDate.DATA}}
        当前进度：{{LogType.DATA}}
        {{remark.DATA}}

     * */
    const AfterSalesServiceProgressReminder = 'kYfE2Vvpj3rBO-xGop4rgWzRSmTIqWFiGMJikVpUqGk';

    /*  @todo 店铺审核通知

        您的店铺已通过审核
        店铺名称：欢乐便利店
        审核结果：审核通过
        处理时间：2016年6月18日18:00
        请前往您的店铺进行更多设置

        {{first.DATA}}
        店铺名称：{{keyword1.DATA}}
        审核结果：{{keyword2.DATA}}
        处理时间：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const Approved = 'mBmJujaigg73D3-EjaAl0zGns89wk7qndzSrmkHFz3M';

    /*  @todo 店铺审核通知

        抱歉，审核未通过。
        店铺名：红旗飘飘
        账户名：123456
        审核意见：请完善您的身份信息
        审核时间：2017-09-01 15:25:12
        请完善后再次提交。

        {{first.DATA}}
        店铺名：{{keyword1.DATA}}
        账户名：{{keyword2.DATA}}
        审核意见：{{keyword3.DATA}}
        审核时间：{{keyword4.DATA}}
        {{remark.DATA}}

     * */
    const FailedPassAudit = 'zgVfayl1PIrzTedtNXeCazBVJ64xXwOQx5zuj9Von-s';

    /*  @todo 新订单通知

        您好，您的订单商家已处理
        店铺名称：便利小店
        订单编号：WY12345620191022183633
        订单内容：红烧仔鸡盖浇饭
        订单金额：15元
        下单时间：2019-10-22 12:12:33
        您好，美味即将送到，请耐心等待~

        {{first.DATA}}
        店铺名称：{{keyword1.DATA}}
        订单编号：{{keyword2.DATA}}
        订单内容：{{keyword3.DATA}}
        订单金额：{{keyword4.DATA}}
        下单时间：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const OrderPlacedSuccess = '3QF_3DnhIB1M8PaYpHxL5Mr-WTrkbTNpPzTS20xnQ6k';

    /*  @todo 提现失败通知

        您的提现申请失败，资金已原路退回。
        提现金额：350元
        提现时间：2017-6-29 17:34:41
        提现状态：已退回
        失败原因：银行卡状态异常
        感谢您的使用。

        {{first.DATA}}
        提现金额：{{keyword1.DATA}}
        提现时间：{{keyword2.DATA}}
        提现状态：{{keyword3.DATA}}
        失败原因：{{keyword4.DATA}}
        {{remark.DATA}}

     * */
    const WithdrawalFailure = 'Xl0JqzbwEMtiCBOGNhFoV2GyZvbi5DTJupZ4CcZ7AD8';

    /*  @todo 售后订单退款申请通过

        商家已同意您的退款申请
        退款金额：599元
        商品详情：正品手串
        订单编号：129344
        为了保证您的利益，请您尽快发货给卖家

        {{first.DATA}}
        退款金额：{{keyword1.DATA}}
        商品详情：{{keyword2.DATA}}
        订单编号：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const OrderRefundSuccess = 'QTHvp5-rsV1l1RFvn94jQXoLw8XqLZozl2Bw19IChNg';
 
    /*  @todo 售后订单退款拒绝通知

        您好，商家暂时拒绝您的退款申请
        订单编码：NO123456789
        售后编码：SH123456789
        拒绝理由：退款理由不充分
        您可联系商家后再发起退款申请。

        {{first.DATA}}
        订单编码：{{keyword1.DATA}}
        售后编码：{{keyword2.DATA}}
        拒绝理由：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const OrderRefundRefuse = 'ITBJ6oyHMPDyhS5Fp7dIMEiU6L4oSpPcFnjHJT2fcXM';

    /*  @todo 退款成功通知

        您订单号为1001768的零库已退款成功
        退款金额：￥1000.00
        退款账户：原路退回
        到账时间：因需要授权，具体到账时间以收到时间为准
        若部分退款或特殊退款要求则以工作人员确认的为准 若有疑问请拨打400--820-7193

        {{first.DATA}}
        退款金额：{{keyword1.DATA}}
        退款账户：{{keyword2.DATA}}
        到账时间：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const RefundSuccessNotification = '-GT9J-mTgeYPlQdmOPvtZ0NuZHLHQJ0zc409CPcJg-o';

    /*  @todo 付款成功通知

        您的下级成功支付订单，您获得一笔分润哟
        下级昵称：下级昵称
        订单编号：201802199837
        订单金额：100元
        佣金金额：0.3元
        时间：2014年7月21日 18:36
        感谢你的使用。

        {{first.DATA}}
        下级昵称：{{keyword1.DATA}}
        订单编号：{{keyword2.DATA}}
        订单金额：{{keyword3.DATA}}
        佣金金额：{{keyword4.DATA}}
        时间：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const SubOrderSubCommission = 'F6dhQneZVsWxMEBFIoDq82rROQruWvLN0eTMXmQo1IY';
}