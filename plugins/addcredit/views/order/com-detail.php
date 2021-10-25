<template id="com-detail">
    <div class="com-detail">
        <el-dialog width="40%" title="订单详情" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <div v-loading="loading">
                <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr class="c4">
                        <td class="label">订单号： </td>
                        <td>{{order.order_no }}</td>
                        <td class="label">手机号：</td>
                        <td>{{order.mobile}}</td>
                    </tr>
                    <tr class="c4">
                        <td class="label">创建时间： </td>
                        <td>{{order.created_at}}</td>
                        <td class="label">支付日期： </td>
                        <td>{{order.pay_at}}</td>
                    </tr>
                    <tr class="c2">
                        <td class="label">状态： </td>
                        <td colspan="3">
                            <span v-if="order.pay_status == 'paid'" style="color:darkgreen">已支付</span>
                            <span v-if="order.pay_status == 'refunding'" style="color:darkred">退款中</span>
                            <span v-if="order.pay_status == 'refund'" style="color:gray">已退款</span>
                        </td>
                    </tr>
                    <tr class="c2">
                        <td class="label">订单金额： </td>
                        <td colspan="3">{{order.order_price}}元</td>
                    </tr>
                    <tr class="c4">
                        <td class="label">现金支付： </td>
                        <td>{{order.pay_price}}元</td>
                        <td class="label">红包支付： </td>
                        <td>{{order.integral_deduction_price}}元</td>
                    </tr>
                    <tr class="c2">
                        <td class="label">平台信息： </td>
                        <td colspan="3">{{platInfo.name}}</td>
                    </tr>
                </table>

                <el-card class="box-card" style="margin-top:20px;">
                    <div slot="header" class="clearfix">
                        <span>充值记录</span>
                        <el-button @click="doRecharge" style="float: right; padding: 3px 0" type="text" v-if="order.pay_status == 'paid'">点击充值</el-button>
                    </div>
                    <el-table :data="records" border style="width: 100%">
                        <el-table-column label="日期" width="100" align="center">
                            <template slot-scope="scope">
                                {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                            </template>
                        </el-table-column>
                        <el-table-column label="状态" width="90" align="center">
                            <template slot-scope="scope">
                                <span v-if="scope.row.process_status == 'processing'" style="color:cornflowerblue">充值中</span>
                                <span v-if="scope.row.process_status == 'fail'" style="color:darkred">失败</span>
                                <span v-if="scope.row.process_status == 'success'" style="color:darkgreen">成功</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="结果">
                            <template slot-scope="scope">
                                {{scope.row.result}}
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-detail', {
        template: '#com-detail',
        props: {
            order: Object,
            visible: Boolean,
            loading: false
        },
        data() {
            return {
                dialogVisible: false,
                content: '',
                platInfo: {name:""},
                records: []
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
                if(val){
                    this.getData();
                }
            }
        },
        methods: {
            doRecharge(){
                let self = this;
                self.$confirm('你确定要执行充值操作吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: 'plugin/addcredit/mall/order/order/recharge',
                        },
                        method: 'post',
                        data: {
                            id: self.order.id
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            this.loadData();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            getData(){
                let that = this;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/addcredit/mall/order/order/detail',
                        id: this.order.id
                    },
                    method: 'get'
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.content = e.data.data.content;
                        that.platInfo = e.data.data.platInfo;
                        that.records = e.data.data.records;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            close(){
                this.$emit('close');
            }
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