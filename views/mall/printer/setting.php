<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item__label {
        padding-right: 20px;
    }

    .el-table {
        z-index: 99;
    }

    .el-card__header {
        padding: 9px 20px
    }

    .el-dialog {
        z-index: 999;
        min-width: 600px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/" style="height: 28px;line-height: 28px;display: inline-block;">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/printer/index'})">打印机管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>打印设置列表</el-breadcrumb-item>
            </el-breadcrumb>
            <el-button style="float: right;" type="primary" size="small"
                       @click="handleEdit(0,0)">添加打印设置
            </el-button>
        </div>
        <div class="table-body">
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='100' prop="id" label="打印机id"></el-table-column>
                <el-table-column width="150" prop="printer.name" label="打印机名称"></el-table-column>
                <el-table-column label="打印方式">
                    <template slot-scope="scope">
                        <el-tooltip style="margin-right: 10px" effect="dark" v-if="scope.row.type.order == '1'" content="下单打印" placement="top">
                                <img src="statics/img/mall/order_print.png" alt="">
                        </el-tooltip>
                        <el-tooltip style="margin-right: 10px" effect="dark" v-if="scope.row.type.pay == '1'" content="付款打印" placement="top">
                                <img src="statics/img/mall/pay_print.png" alt="">
                        </el-tooltip>
                        <el-tooltip style="margin-right: 10px" effect="dark" v-if="scope.row.type.confirm == '1'" content="确认收货打印" placement="top">
                                <img src="statics/img/mall/confirm_print.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="store_name" label="所属门店"></el-table-column>
                <el-table-column label="是否使用规格" width="120">
                    <template slot-scope="scope">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                @change="change(scope.row)"
                                v-model="scope.row.is_attr">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="是否启用" width="100">
                    <template slot-scope="scope">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                @change="change(scope.row)"
                                v-model="scope.row.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button type="text" @click="handleEdit(scope.$index, scope.row,list.id)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="handleDel(scope.$index, scope.row,list.id)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            @current-change="pageChange"
                            layout="prev, pager, next"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog title="打印设置" :visible.sync="dialog" width="35%">
        <el-form v-loading="load" :model="form" label-width="10rem" label-position="right">
            <el-form-item label="选择打印机" prop="select">
                <el-select filterable v-model="form.printer_id" size="small">
                  <el-option :label="item.name" :value="item.id" :key="item.id" v-for="item in select"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="选择门店" prop="select">
                <el-select filterable v-model="form.store_id" size="small">
                    <el-option :label="item.name" :value="item.id" :key="item.id" v-for="item in stores"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="是否打印规格" prop="is_attr">
                <el-switch v-if="!load"
                    v-if="dialog"
                    v-model="form.is_attr"
                    :active-value="1"
                    :inactive-value="0">
                </el-switch>
            </el-form-item>
            <el-form-item label="订单打印方式" prop="type">
                <el-checkbox-group v-if="!load" v-model="type">
                    <el-checkbox label="order">下单打印</el-checkbox>
                    <el-checkbox label="pay">付款打印</el-checkbox>
                    <el-checkbox label="confirm">确认收货打印</el-checkbox>
                </el-checkbox-group>
            </el-form-item>
            <el-form-item label="是否启用" prop="status">
                <el-switch v-if="!load"
                    v-if="dialog"
                    v-model="form.status"
                    :active-value="1"
                    :inactive-value="0">
                </el-switch>
            </el-form-item>
            <el-form-item style="margin-bottom: 0">
                <el-button type="primary" size="small" style="float: right;width: 80px;margin-right: 20px;" :loading="submitLoading" @click="onSubmit">提交</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                load: false,
                submitLoading: false,
                dialog: false,
                type: [],
                list: [],
                select:[],
                form: {
                    is_attr: '0',
                    status: '0',
                    type: {
                        order: '0',
                        pay: '0',
                        confirm: '0',
                    },
                    store_id: 0,
                },
                pagination: null,
                stores: [],
            };
        },
        methods: {
            //分页
            pageChange(page) {
                this.loading = true;
                loadList('mall/printer/setting',page).then(e => {
                    this.loading = false;
                    this.list = e.list;
                    this.pagination = e.pagination;
                });
            },
            // 编辑
            handleEdit(row, column) {
                this.load = true;
                this.dialog = true;
                this.type = [];
                let params = {
                        r: 'mall/printer/setting-edit',
                    }
                if(column != 0) {
                    params = {
                        r: 'mall/printer/setting-edit',
                        id: column.id,
                    }
                }
                request({
                    params: params,
                }).then(e => {
                    this.load = false;
                    if (e.data.code == 0) {
                        this.select = e.data.data.select;
                        this.stores = e.data.data.stores;
                        this.form = {
                            is_attr: '0',
                            printer_id: '0',
                            status: '0',
                            type: {
                                order: '0',
                                pay: '0',
                                confirm: '0',
                            },
                            store_id: 0,
                        }
                        if(e.data.data.list != null) {
                            this.form = e.data.data.list;
                        }else {
                            this.form.printer_id = this.select[0].id;
                        }
                        let type = e.data.data.list.type;
                        if (type.order == 1) {
                            this.type.push("order")
                        }
                        if (type.pay == 1) {
                            this.type.push("pay")
                        }
                        if (type.confirm == 1) {
                            this.type.push("confirm")
                        }
                    }
                }).catch(e => {
                });
            },
            // 修改
            change(e) {
                request({
                    params: {
                        r: 'mall/printer/setting-edit',
                    },
                    data: e,
                    method: 'post'
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.$message({
                          message: e.data.msg,
                          type: 'success'
                        });
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                }).catch(e => {
                    this.submitLoading = false;
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            },

            // 提交
            onSubmit() {
                if (this.type.indexOf('order') !== -1) {
                    this.form.type.order = 1;
                }else {
                    this.form.type.order = 0;
                }
                if (this.type.indexOf('pay') !== -1) {
                    this.form.type.pay = 1;
                }else {
                    this.form.type.pay = 0;
                }
                if (this.type.indexOf('confirm') !== -1) {
                    this.form.type.confirm = 1;
                }else {
                    this.form.type.confirm = 0;
                }
                this.submitLoading =true;
                let para = Object.assign(this.form);
                request({
                    params: {
                        r: 'mall/printer/setting-edit',
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.$message({
                          message: e.data.msg,
                          type: 'success'
                        });
                        setTimeout(function(){
                            location.reload();
                        },300);
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                }).catch(e => {
                    this.submitLoading = false;
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            },

            //删除
            handleDel: function(row, column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = { id: column.id};
                    request({
                        params: {
                            r: 'mall/printer/setting-destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                        const h = this.$createElement;
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        setTimeout(function(){
                            location.reload();
                        },300);
                    }else{
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                    }
                    }).catch(e => {
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                    });
                }).catch(() => {
                    this.$message.info('已取消删除')
                });
            }
        },
        created() {
            this.loading = true;
            // 获取列表
            loadList('mall/printer/setting').then(e => {
                this.$message({
                  message: '请求成功',
                  type: 'success'
                });
                this.loading = false;
                this.list = e.list;
                this.select = e.select;
                this.pagination = e.pagination;
            });
        }
    })
</script>