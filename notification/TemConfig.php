<?php

namespace app\notification;

class TemConfig
{
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

    /*  @todo 预订成功通知

        您已完成支付，预订成功。
        酒店名称：香格里拉
        预订房型：豪华房
        入住日期：2017年05月02日
        预订房数：1
        订单金额：800
        点击这里查看预订详情

        {{first.DATA}}
        酒店名称：{{keyword1.DATA}}
        预订房型：{{keyword2.DATA}}
        入住日期：{{keyword3.DATA}}
        预订房数：{{keyword4.DATA}}
        订单金额：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const HotelReservationSuccessful = 'NucwFyosk892dUIijadXgOtXfoTys9xMtC_d-V8EGhY';

    /*  @todo 订单取消失败通知

        抱歉，您的订单取消失败
        订单编号：1000029920
        酒店名称：丽思卡尔顿
        预订房型：豪华大床房
        房间数量：1间（2晚）
        入住时间：2018年3月21
        如有疑问请及时联系我们的客服小妹。（点击查看更多详情）

        {{first.DATA}}
        订单编号：{{keyword1.DATA}}
        酒店名称：{{keyword2.DATA}}
        预订房型：{{keyword3.DATA}}
        房间数量：{{keyword4.DATA}}
        入住时间：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const OrderCancellationFailed = '9Etr9bc8p-rgOEmG6RiPdZMz9oZKBjArR-mh4gtQVEQ';

    /*  @todo 提现到账通知

        你好，奖金余额已提现到账
        提现金额：100.00元
        提现账户：招商银行尾号0449
        提现时间：2016-04-02 11:45:08
        到账时间：2016-04-03 11:45:08
        你的奖金已提现至银行卡，请查收！

        {{first.DATA}}
        提现金额：{{keyword1.DATA}}
        提现账户：{{keyword2.DATA}}
        提现时间：{{keyword3.DATA}}
        到账时间：{{keyword4.DATA}}
        {{remark.DATA}}

     * */
    const NoticeOfWithdrawalAndReceipt = '5RSmgUU2UopOxXN4zjdtHrJZle-xsVJlkjmgig5TUys';

    /*  @todo 	成员加入提醒

        您好，您的xx有新成员加入
        姓名：李永强
        时间：2015.10.1 8:00
        您可以到xx管理后台管理您的源网成员。

        {{first.DATA}}
        姓名：{{keyword1.DATA}}
        时间：{{keyword2.DATA}}
        {{remark.DATA}}

     * */
    const MemberJoinReminder = 'YdqBUmMmRYlCg5Kfp8JiGLGUqsVV-_Z8MJy4KN1yucU';

    /*  @todo 	收益到账通知

        收益到账通知：
        收益金额：220.30元
        收益来源：荣事达旗舰店
        到账时间：2014年7月21日 18:36
        您的收益已到账。

        {{first.DATA}}
        收益金额：{{keyword1.DATA}}
        收益来源：{{keyword2.DATA}}
        到账时间：{{keyword3.DATA}}
        {{remark.DATA}}

     * */
    const IncomeArrival = '-fSmrfitWwJn1Lgr488aaJ2Q3BOAccuD3kHuHdi7BpE';

    /*  @todo 	订单支付成功提醒

        恭喜您！购买的商品已支付成功，请留意物流信息哦！么么哒！~~
        订单编号：201807011745324648
        商品名称：加硬快递纸箱
        订单总价：1.00元
        订单状态：已支付
        下单时间：2018年7月22日 10:10
        欢迎您的到来！

        {{first.DATA}}
        订单编号：{{keyword1.DATA}}
        商品名称：{{keyword2.DATA}}
        订单总价：{{keyword3.DATA}}
        订单状态：{{keyword4.DATA}}
        下单时间：{{keyword5.DATA}}
        {{remark.DATA}}

     * */
    const STORE_PAY = 'zkMsjE9-pjgANoP5JsKAAFPpx2zi37q-0bLYy348wN4';

    /*  @todo 	订单支付成功提醒

        尊敬的会员，您好！您已消费成功，详情如下：
        消费门店：积上下沙商贸城店
        消费金额：2538元
        获得积分：3538分
        消费时间：42018年3月28日
        当前可用积分：51338分
        欢迎您再次光临！

        {{first.DATA}}
        消费门店：{{keyword1.DATA}}
        消费金额：{{keyword2.DATA}}
        获得积分：{{keyword3.DATA}}
        消费时间：{{keyword4.DATA}}
        当前可用积分：{{keyword5.DATA}}
        {{remark.DATA}}
     * */
    const VOUCHER_ORDER_PAY = 'Z3yCFkv5uZAuKFMUfCP05Z3B_TSLa4tznFgnb7ir6U8';
}