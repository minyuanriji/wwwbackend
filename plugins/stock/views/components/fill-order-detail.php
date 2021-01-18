<template id="fill-order-detail">
    <el-dialog :visible.sync="edit.visible" width="20%" title="订单详情">
        <div>

            <el-table :data="goods_list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                     >
                <el-table-column prop="id" width="80" label="ID"></el-table-column>
                <el-table-column label="商品信息" width="150">
                    <template slot-scope="scope">
                        <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                   :src="scope.row.goods.cover_pic"></com-image>
                        <div>{{scope.row.goods.name}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="拿货数量" prop="num">
                </el-table-column>
                <el-table-column label="总价" prop="price">
                </el-table-column>
                <el-table-column label="商品售价" prop="sale_price">

                </el-table-column>
                <el-table-column label="补货奖励" prop="fill_price">

                </el-table-column>

            </el-table>


        </div>

    </el-dialog>
</template>
<script>
    Vue.component('fill-order-detail', {
        template: '#fill-order-detail',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            order_id: {
                type: Number,
                default: 0

            }
        },
        data() {
            return {
                loading:false,
                edit: {
                    visible: false
                }

                ,goods_list:[],
            }
        },
        mounted() {

            console.log(this.order_id)

        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                    this.loadDetail(this.order_id)
                }
            }
            ,
            'edit.visible'() {
                if (!this.edit.visible) {
                   this.editCancel();
                }
            }
        },

        methods: {
            loadDetail(order_id) {

                this.loading = true;
                let params = {
                    r: 'plugin/stock/mall/order/fill-order-detail',
                    order_id: order_id
                };
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading=false;
                    if (e.data.code == 0) {
                        this.goods_list = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            editCancel(){
                this.$emit('input', false);
            },
        }
    });
</script>
