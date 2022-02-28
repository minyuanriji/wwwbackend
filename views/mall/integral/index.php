<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .input-item {
        display: inline-block;
        width: 200px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 15px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>积分自动发放列表</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/integral/edit'})">添加自动发放方案
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="keyword" clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID"></el-table-column>

                <el-table-column prop="controller_type" label="类型">
                  <template slot-scope="scope">
                    <span v-if="scope.row.controller_type == 0" style="color: green">积分券</span>
                    <span v-if="scope.row.controller_type == 1" style="color: red">红包</span>
                  </template>
                </el-table-column>

                <el-table-column prop="user.nickname" label="用户">
                    <template slot-scope="scope">
                        <span>{{scope.row.user.nickname}}</span>
                    </template>
                </el-table-column>

                <el-table-column label="面值" width="80">
                    <template slot-scope="scope">{{scope.row.integral_num}}</template>
                </el-table-column>

                <el-table-column label="周期" width="80">
                    <template slot-scope="scope">
                        {{scope.row.period}}
                        <span v-if="scope.row.period_unit == 'week'">周</span>
                        <span v-if="scope.row.period_unit == 'month'">月</span>
                    </template>
                </el-table-column>
<!--
                <el-table-column prop="period_unit" label="周期单位" width="80">
                    <template slot-scope="scope">
                        <span v-if="scope.row.period_unit == 'week'">周</span>
                        <span v-if="scope.row.period_unit == 'month'">月</span>
                    </template>
                </el-table-column>-->

                <el-table-column prop="type" label="积分类型">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1" style="color: red">永久积分</span>
                        <span v-if="scope.row.type == 2" style="color: green">动态积分</span>
                    </template>
                </el-table-column>

                <el-table-column prop="status" label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 0">未发放</span>
                        <span v-if="scope.row.status == 1" style="color: green">发放中</span>
                        <span v-if="scope.row.status == 2" style="color: #2E9FFF">已完成</span>
                    </template>
                </el-table-column>

                <el-table-column prop="effective_days" label="有效天数" width="80">
                    <template slot-scope="scope">{{scope.row.effective_days}}</template>
                </el-table-column>

                <el-table-column prop="next_publish_time" width="180" label="下次发放时间">
                    <template slot-scope="scope">
                        {{scope.row.next_publish_time|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>

                <el-table-column prop="desc" label="积分描述" width="180">
                    <template slot-scope="scope">{{scope.row.desc}}</template>
                </el-table-column>

                <el-table-column prop="created_at" width="180" label="创建时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>

                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <!--<el-button size="mini" circle type="text" @click="handleEdit(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>                        
                        </el-button>-->
                        <!--<el-button size="mini" circle type="text" @click="handleDel(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>-->
                    </template>
                </el-table-column>

            </el-table>
            <div>
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        :page-size="pagination.pageSize"
                        @current-change="pageChange"
                        layout="prev, pager, next"
                        :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                },
                keyword: '',
                pageCount: 0,

                loading: false,
                list: [],
                pagination: null,
            };
        },

        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/integral/index',
                        page: this.page,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.loading = false;
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
            },

            //带着ID前往编辑页面
            handleEdit: function(row, column)
            {
                navigateTo({r: 'mall/integral/edit',id:column.id});
            },


            //分页
            pageChange(page) {
                this.loading = true;
                this.page = page;
                this.getList();
            },

            //删除
            handleDel: function(row, column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = { id: column.id};
                    request({
                        params: {
                            r: 'mall/coupon-auto-send/destroy'
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
                })
            }
        },
        created() {
            this.loading = true;
            // 获取列表
            this.getList();
        }
    })
</script>
