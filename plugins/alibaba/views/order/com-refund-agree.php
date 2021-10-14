
<template id="com-refund-agree">
    <div class="com-refund-agree">
        <el-dialog title="同意退款操作" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr><th colspan="4">订单详情</th></tr>
                <tr class="c4">
                    <td class="label">创建时间：</td>
                    <td>{{ refundNewData.created_at|dateTimeFormat('Y-m-d H:i:s') }}</td>
                    <td class="label">订单号： </td>
                    <td>{{ refundNewData.order_no }}</td>
                </tr>
                <tr class="c4">
                    <td class="label">支付用户：</td>
                    <td>{{ refundNewData.nickname }}(ID:{{refundNewData.user_id}})</td>
                    <td class="label">支付时间：</td>
                    <td>{{ refundNewData.pay_at|dateTimeFormat('Y-m-d H:i:s') }}</td>
                </tr>
                <tr class="c2">
                    <td class="label">商品信息：</td>
                    <td colspan="3">
                        <div style="display: flex">
                            <com-image style="width:80px;height:80px;" :src="refundNewData.cover_url"></com-image>
                            <div style="flex-direction:column;justify-content:space-between;margin-left:20px;display: flex">
                                <div>小计：{{ refundNewData.unit_price }}</div>
                                <div>数量：× {{ refundNewData.num }}</div>
                                <div>规格：{{ refundNewData.sku_labels[0] }}</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr><th colspan="4">1688状态</th></tr>
                <tr class="c4">
                    <td class="label">订单状态：</td>
                    <td v-if="agreeBackNewData.orderStatus=='waitbuyerpay'">等待买家付款</td>
                    <td v-if="agreeBackNewData.orderStatus=='waitsellersend'">等待卖家发货</td>
                    <td v-if="agreeBackNewData.orderStatus=='waitbuyerreceive'">等待买家收货</td>
                    <td v-if="agreeBackNewData.orderStatus=='confirm_goods'">已收货</td>
                    <td v-if="agreeBackNewData.orderStatus=='success'">交易成功</td>
                    <td v-if="agreeBackNewData.orderStatus=='cancel'">交易取消</td>
                    <td v-if="agreeBackNewData.orderStatus=='terminated'">交易终止</td>
                    <td v-if="agreeBackNewData.orderStatus=='未枚举'">其他状态</td>
                    <td class="label">售后状态： </td>
                    <td v-if="agreeBackNewData.refundStatus=='waitselleragree '">等待卖家同意</td>
                    <td v-if="agreeBackNewData.refundStatus=='waitbuyermodify '">待买家修改</td>
                    <td v-if="agreeBackNewData.refundStatus=='waitbuyersend '">等待买家退货</td>
                    <td v-if="agreeBackNewData.refundStatus=='waitsellerreceive '">等待卖家确认收货</td>
                    <td v-if="agreeBackNewData.refundStatus=='refundsuccess '">退款成功</td>
                    <td v-if="agreeBackNewData.refundStatus=='refundclose '">退款失败</td>
                    <td v-if="agreeBackNewData.refundStatus==''"></td>
                </tr>
            </table>
            <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr><th colspan="4">退款金额</th></tr>
                <tr class="c4">
                    <td class="label">购物卷：</td>
                    <td>{{ agreeBackNewData.shoppingVoucherRefund.real_amount }}</td>
                    <td>
                        <el-button @click="payment(agreeBackNewData.shoppingVoucherRefund, 'paid')" type="text"  size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                <img src="statics/img/mall/pay.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="apply(agreeBackNewData.shoppingVoucherRefund, 'cancel')" type="text" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                <img src="statics/img/mall/nopass.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </td>
                </tr>
            </table>
        </el-dialog>
    </div>
</template>

<script>

    Vue.component('com-refund-agree', {
        template: '#com-refund-agree',
        props: {
            visible: Boolean,
            refundData:Object,
            agreeBackData:Object,
        },
        data() {
            return {
                dialogTitle: "添加应用",
                dialogVisible: false,
                refundNewData: '',
                agreeBackNewData: '',
                rules: {

                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            refundData(val, oldVal){
                this.refundNewData = val;
            },
            agreeBackData(val, oldVal){
                this.agreeBackNewData = val;
            },
        },
        methods: {
            payment(row, refund_status){
                this.apply(row, refund_status, function (rs){
                    console.log(rs);
                    this.dialogVisible = false;
                });
            },
            close(){
                this.$emit('close');
            },
            apply(row, act, fn) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/alibaba/mall/order/sale-payment',
                                },
                                method: 'post',
                                data: {
                                    id: row.id,
                                    act: act,
                                    content: instance.inputValue,
                                    aliRefundStatus:this.agreeBackNewData.refundStatus,
                                }
                            }).then(e => {
                                instance.confirmButtonLoading = false;
                                if (e.data.code === 0) {
                                    this.dialogVisible = false;
                                    this.$navigate({
                                        r: 'plugin/alibaba/mall/order/refund-list',
                                    });
                                    done();
                                    if(typeof fn == "function"){
                                        fn.call(this, e.data);
                                    }
                                } else {
                                    instance.confirmButtonText = '确定';
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        }else{
                            done();
                        }
                    }
                });
            },
        },
        created(){

        }
    });
</script>
<style>
    .grid-i th{padding:5px 0px 5px 0px;}
    .grid-i th,.grid-i td{text-align:left;}
    .grid-i td{padding:10px 10px;border:1px solid #ddd;border-bottom:none;}
    .grid-i tr:last-child td{border-bottom:1px solid #ddd;}
    .grid-i .label{border-left:none;font-weight:bold;padding:6px 6px 6px 0px;border-right:none;text-align:right;background:#f1f1f1;}
    .grid-i td:first-child{border-left:1px solid #ddd;}
    .grid-i .c4 td{width:30%}
    .grid-i .c2 td{width:80%}
    .grid-i .label{width:20% !important;}
</style>