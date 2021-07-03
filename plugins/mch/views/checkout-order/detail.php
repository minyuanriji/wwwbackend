<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>结账单详情</span>
                <div style="float: right;margin-top: -5px"> </div>
            </div>
        </div>
        <div class="form-body">
            <div>订单号：{{order.order_no}}</div>
            <div>店铺名称：{{order.mchStore.name}}</div>
            <span>支付状态：
                <span v-if="order.is_pay == 1">已支付</span>
                <span v-else>未支付</span>
            </div>
            <template v-if="order.is_pay == 1">
                <div>支付用户：{{order.payUser.nickname}}</div>
                <div>支付时间：{{order.format_pay_time}}</div>
            </template>
            <el-button @click="queryReload()"  v-if="order.is_pay != 1">刷新状态</el-button>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                order: {}
            };
        },
        methods: {
            getDetail() {
                request({
                    params: {
                        r: "plugin/mch/mall/checkout-order/detail",
                        id: getQuery('id'),
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.order = e.data.data.detail;
                    }
                }).catch(e => {

                });
            },
            queryReload(){
                request({
                    params: {
                        r: "plugin/mch/mall/checkout-order/query-reload",
                        id: getQuery('id'),
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.order = e.data.data.detail;
                    }
                }).catch(e => {

                });
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
