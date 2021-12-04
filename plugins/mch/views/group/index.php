<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .table-body .el-form-item {
        margin-bottom: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }
    .el-tooltip__popper{max-width: 400px}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>连锁店管理</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加总店</el-button>
                </div>
            </div>
        </div>

        <div class="table-body">
            <el-form @submit.native.prevent="searchList" size="small" :inline="true" :model="search">
                <el-form-item>
                    <div class="input-item">
                        <el-input  @keyup.enter.native="searchList" size="big" placeholder="请输入关键词搜索" v-model="search.keyword" clearable
                                   @clear='searchList'>
                            <el-button @click="searchList" slot="append" icon="el-icon-search" ></el-button>
                            <el-select style="width: 130px" slot="prepend" v-model="search.keyword1">
                                <el-option v-for="item in selectList" :key="item.value"
                                           :label="item.name"
                                           :value="item.value">
                                </el-option>
                            </el-select>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="60"> </el-table-column>
                <el-table-column :show-overflow-tooltip="true" label="名称" width="350">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                            <div style="margin-left: 10px;width: 300px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="手机号" prop="mobile" width="150"></el-table-column>
                <el-table-column label="绑定用户" prop="user" width="350">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="50" height="50" :src="scope.row.avatar_url"></com-image>
                            <div style="margin-left: 10px;width: 300px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.user_name}}(ID:{{scope.row.user_id}})</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="商户ID" prop="mch_id" width="100"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
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
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                search: {
                    keyword: '',
                    keyword1: 'mch_mobile',
                },
                btnLoading: false,
                selectList : [
                    {
                        name : '商户手机号',
                        value : 'mch_mobile',
                    },
                    {
                        name : '店铺名',
                        value : 'store_name',
                    },
                    {
                        name : '用户ID',
                        value : 'user_id',
                    },
                    {
                        name : '商户ID',
                        value : 'mch_id',
                    },
                ],
            };
        },
        methods: {
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/list',
                        page: self.page,
                        keyword: self.search.keyword,
                        keyword1: self.search.keyword1
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/mch/mall/group/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/mch/mall/group/edit',
                    });
                }
            },
            deleteIt(item){
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/group/delete',
                        },
                        method: 'post',
                        data: {
                            id: item.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error("请求失败");
                        self.listLoading = false;
                    });
                }).catch(() => {

                });
            },
            searchList() {
                this.page = 1;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });

</script>
