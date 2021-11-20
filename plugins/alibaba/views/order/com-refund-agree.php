
<template id="com-refund-agree">
    <div class="com-refund-agree">
        <el-dialog title="同意退款操作" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <div v-loading="loading">
                <template v-if="order != null">
                    <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                        <tr><th colspan="4">订单详情</th></tr>
                        <tr class="c4">
                            <td class="label">创建时间：</td>
                            <td>{{order.created_at|dateTimeFormat('Y-m-d H:i:s') }}</td>
                            <td class="label">订单号： </td>
                            <td>{{order.order_no }}</td>
                        </tr>
                        <tr class="c4">
                            <td class="label">支付用户：</td>
                            <td>{{user.nickname }}(ID:{{user.id}})</td>
                            <td class="label">支付时间：</td>
                            <td>{{order.pay_at|dateTimeFormat('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr class="c2">
                            <td class="label">商品信息：</td>
                            <td colspan="3">
                                <div style="display: flex">
                                    <com-image style="width:80px;height:80px;" :src="goods.cover_url"></com-image>
                                    <div style="flex-direction:column;justify-content:space-between;margin-left:20px;display: flex">
                                        <div>小计：{{ detail.unit_price }}</div>
                                        <div>数量：× {{ detail.num }}</div>
                                        <div>规格：{{ detail.sku_labels }}</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table class="grid-i" cellpadding="0" cellspacing="0" style="margin-top:10px;width:100%;">
                        <tr><th colspan="4">1688状态</th></tr>
                        <tr class="c4">
                            <td class="label">订单状态：</td>
                            <td v-if="ali_orderdata.baseInfo.status=='waitbuyerpay'">等待买家付款</td>
                            <td v-if="ali_orderdata.baseInfo.status=='waitsellersend'">等待卖家发货</td>
                            <td v-if="ali_orderdata.baseInfo.status=='waitbuyerreceive'">等待买家收货</td>
                            <td v-if="ali_orderdata.baseInfo.status=='confirm_goods'">已收货</td>
                            <td v-if="ali_orderdata.baseInfo.status=='success'">交易成功</td>
                            <td v-if="ali_orderdata.baseInfo.status=='cancel'">交易取消</td>
                            <td v-if="ali_orderdata.baseInfo.status=='terminated'">交易终止</td>
                            <td v-if="ali_orderdata.baseInfo.status=='未枚举'">其他状态</td>
                            <td class="label">售后状态： </td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='waitselleragree '">等待卖家同意</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='waitbuyermodify '">待买家修改</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='waitbuyersend '">等待买家退货</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='waitsellerreceive '">等待卖家确认收货</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='refundsuccess '">退款成功</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus=='refundclose '">退款失败</td>
                            <td v-if="ali_orderdata.baseInfo.refundStatus==''"></td>
                        </tr>
                    </table>

                    <el-table :data="refundData" style="margin-top:20px;width: 100%">
                        <el-table-column prop="id" label="编号" width="110"></el-table-column>
                        <el-table-column label="状态" width="150">
                            <template slot-scope="scope">
                                <span style="color:darkred;" v-if="scope.row.status == 'waitting'">待打款</span>
                                <span style="color:darkgreen;" v-if="scope.row.status == 'paid'">已打款</span>
                                <span style="color:gray;" v-if="scope.row.status == 'cancel'">已取消</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="打款类型" width="150">
                            <template slot-scope="scope">
                                <span v-if="scope.row.refund_type == 'score'">积分</span>
                                <span v-if="scope.row.refund_type == 'integral'">红包</span>
                                <span v-if="scope.row.refund_type == 'money'">现金</span>
                                <span v-if="scope.row.refund_type == 'balance'">余额</span>
                                <span v-if="scope.row.refund_type == 'shopping_voucher'">购物券</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="金额/数量" width="200">
                            <template slot-scope="scope">
                                <span>{{scope.row.refund_amount}}</span>
                                <span v-if="scope.row.order_detail_id == 0" style="color:steelblue">（运费）</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button @click="doPaid(scope.row, 'paid')" type="text" size="mini" circle v-if="scope.row.status == 'waitting'">
                                    <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                        <img src="statics/img/mall/pass.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="doPaid(scope.row, 'cancel')" type="text" size="mini" circle v-if="scope.row.status == 'waitting'">
                                    <el-tooltip class="item" effect="dark" content="取消" placement="top">
                                        <img src="statics/img/mall/nopass.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>


                </template>
            </div>
        </el-dialog>
    </div>
</template>

<script>

    Vue.component('com-refund-agree', {
        template: '#com-refund-agree',
        props: {
            orderDetailId: Number
        },
        data() {
            return {
                loading: true,
                order: null,
                detail: {},
                detail_1688: {},
                ali_orderdata: {},
                goods: {},
                user: {},
                currentId: 0,
                refundData: []
            };
        },
        computed:{
            dialogVisible: function (){
                return this.currentId > 0 ? true : false;
            }
        },
        watch: {
            orderDetailId(val, oldVal){
                this.currentId = parseInt(val);
                if(this.currentId > 0){
                    this.getOrderDetail();
                }
            }
        },
        methods: {
            getOrderDetail(){
                let that = this;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/alibaba/mall/order/order-detail',
                        id: this.orderDetailId
                    },
                    method: 'get'
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.order         = e.data.data.order;
                        that.detail        = e.data.data.detail;
                        that.detail_1688   = e.data.data.detail_1688;
                        that.ali_orderdata = e.data.data.ali_orderdata;
                        that.goods         = e.data.data.goods;
                        that.user          = e.data.data.user;
                        that.refundData    = e.data.data.refund_datas;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            payment(row, refund_status){
                this.apply(row, refund_status, function (rs){
                    this.dialogVisible = false;
                });
            },
            close(){
                this.$emit('close');
            },
            doPaid(row, act) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/alibaba/mall/order/refund-paid',
                                },
                                method: 'post',
                                data: {
                                    refund_id: row.id,
                                    order_detail_id:this.orderDetailId,
                                    act: act,
                                    remark: instance.inputValue
                                }
                            }).then(e => {
                                instance.confirmButtonLoading = false;
                                instance.confirmButtonText = '确定';
                                if (e.data.code === 0) {
                                    this.getOrderDetail();
                                    done();
                                } else {
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
        created(){}
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