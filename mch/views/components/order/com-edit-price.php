<style>

</style>

<template id="com-edit-price">
    <div class="com-edit-price">
        <!-- 改价 -->
        <el-dialog title="修改价格" :visible.sync="dialogVisible" width="20%" @close="closeDialog">
            <el-form label-width="80px">
                <el-form-item label="商品总价">
                    <el-input size="small" type="number" v-model="order_price.total_goods_price"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="运费">
                    <el-input size="small" type="number" v-model="order_price.express_price"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right;margin-bottom: 0">
                    <el-button size="mini" type="primary" @click="dialogVisible = false">取消
                    </el-button>
                    <el-button size="mini" type="primary" :loading="submitLoading" @click="changeTotalPrice">确 认
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-edit-price', {
        template: '#com-edit-price',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            }
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openDialog()
                }
            }
        },
        data() {
            return {
                dialogVisible: false,
                submitLoading:false,
                order_price: {
                    total_goods_price: 0,
                    express_price: 0,
                },
            }
        },
        methods: {
            // 打开备注
            openDialog() {
                this.dialogVisible = true;
                this.order_price.total_goods_price = this.order.total_goods_price;
                this.order_price.express_price = this.order.express_price;
            },
            closeDialog() {
                this.$emit('close')
            },
            // 修改总价
            changeTotalPrice() {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mch/order/update-total-price',
                    },
                    data: {
                        order_id: this.order.id,
                        total_price: this.order_price.total_goods_price,
                        express_price: this.order_price.express_price
                    },
                    method: 'post',
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.dialogVisible = false;
                        this.$emit('submit');
                        this.$message({
                            message: '修改成功',
                            type: 'success'
                        });
                        this.getList();
                    } else {
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }
                }).catch(e => {
                });
            },
        }
    })
</script>