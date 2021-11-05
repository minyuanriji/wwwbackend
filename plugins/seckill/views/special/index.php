<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>秒杀专题列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加秒杀专题</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="getList">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table ref="multipleTable" v-loading="listLoading" :data="list" border style="width: 100%">

                <el-table-column prop="id" label="ID" width="100"></el-table-column>

                <el-table-column label="专题名称" >
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.pic_url"></com-image>
                            </div>
                            <div flex="cross:top cross:center">
                                <div flex="dir:left">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.name}}</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.name}}</com-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="created_at" width="220" label="开始时间">
                    <template slot-scope="scope">
                        <div>{{scope.row.start_time}}</div>
                    </template>
                </el-table-column>

                <el-table-column prop="created_at" width="220" label="结束时间">
                    <template slot-scope="scope">
                        <div>{{scope.row.end_time}}</div>
                    </template>
                </el-table-column>

                <el-table-column prop="created_at" width="220" label="添加日期">
                    <template slot-scope="scope">
                        <div>{{scope.row.created_at}}</div>
                    </template>
                </el-table-column>

                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row.id, scope.$index)" circle type="text" size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="display: flex;justify-content: space-between;margin-top:20px;">
                <el-pagination
                        v-if="pageCount > 0"
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
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
                keyword: '',
                pageCount: 0,
                sort: 0,
                id: null,
            };
        },
        methods: {
            search: function() {
                this.listLoading = true;
                let keyword = this.keyword;
                request({
                    params: {
                        r: 'plugin/seckill/mall/special/special',
                        keyword: keyword
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }else{
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                }
                }).catch(e => {
                    this.listLoading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });        
            },

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
                        r: 'plugin/seckill/mall/special/special',
                        page: self.page,
                        keyword: self.keyword,
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
                        r: 'plugin/seckill/mall/special/special/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/seckill/mall/special/special/edit',
                    });
                }
            },

            destroy(id, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/seckill/mall/special/special/destroy',
                        },
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
