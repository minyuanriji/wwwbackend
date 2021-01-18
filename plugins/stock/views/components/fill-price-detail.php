<template id="fill-price-detail">
    <el-dialog :visible.sync="edit.visible" width="20%" title="订单详情">
        <div>
            <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                <el-table-column prop="id" width="80" label="ID"></el-table-column>
                <el-table-column label="用户信息" width="200">
                    <template slot-scope="scope">
                        <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                   :src="scope.row.avatar_url"></com-image>
                        <div>{{scope.row.nickname}}</div>
                        <div>{{scope.row.user_level_name}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="收益" prop="price">
                </el-table-column>
                <el-table-column label="收益类型">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type==1">平级奖</div>
                        <div v-if="scope.row.type==0">货款</div>
                    </template>
                </el-table-column>
                <el-table-column label="发放时间" prop="created_at"></el-table-column>
            </el-table>
        </div>
    </el-dialog>
</template>
<script>
    Vue.component('fill-price-detail', {
        template: '#fill-price-detail',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            log_id: {
                type: Number,
                default: 0

            }
        },
        data() {
            return {
                loading: false,
                edit: {
                    visible: false
                },
                list: [],
            }
        },
        mounted() {

            console.log(this.log_id)

        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                    this.loadDetail(this.log_id)
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
            loadDetail(log_id) {

                this.loading = true;
                let params = {
                    r: 'plugin/stock/mall/order/fill-price-detail',
                    log_id: log_id
                };
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            editCancel() {
                console.log('关闭');
                this.$emit('input', false);
            },
        }
    });
</script>
