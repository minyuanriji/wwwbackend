
<template id="com-refund-agree">
    <div class="com-refund-agree">
        <el-dialog title="同意退款操作" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr><th colspan="4">订单详情</th></tr>
                <tr class="c4">
                    <td class="label">创建时间：</td>
                    <td>2021-09-30 10:58:22</td>
                    <td class="label">订单号： </td>
                    <td>ALIS202109301058211363736578</td>
                </tr>
                <tr class="c4">
                    <td class="label">支付用户：</td>
                    <td>牛翻天2021(ID:865)</td>
                    <td class="label">支付时间：</td>
                    <td>2021-09-30 11:03:18</td>
                </tr>
                <tr class="c2">
                    <td class="label">商品信息：</td>
                    <td colspan="3">
                        <div style="display: flex">
                            <com-image style="width:80px;height:80px;" src="http://cbu01.alicdn.com/img/ibank/2019/002/423/13032324200_1743134081.jpg"></com-image>
                            <div style="flex-direction:column;justify-content:space-between;margin-left:20px;display: flex">
                                <div>小计：10.00</div>
                                <div>数量：× 1</div>
                                <div>规格：颜色:豆绿</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <table class="grid-i" cellpadding="0" cellspacing="0" style="width:100%;">
                <tr><th colspan="4">1688状态</th></tr>

            </table>
        </el-dialog>
    </div>
</template>

<script>

    Vue.component('com-refund-agree', {
        template: '#com-refund-agree',
        props: {
            visible: Boolean
        },
        data() {
            return {
                dialogTitle: "添加应用",
                dialogVisible: false,
                rules: {

                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            }
        },
        methods: {
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