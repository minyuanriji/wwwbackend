<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分红明细</span>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"
                          placeholder="请输入关键词进行搜索"
                          v-model="keyword"
                          clearable
                          @clear="search">
                    <el-select slot="prepend" v-model="kw_type" placeholder="请选择" size="small"
                               style="width:120px;">
                        <el-option v-for="item in item_type_options"
                                   :key="item.value"
                                   :label="item.label"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">

                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>

                <el-table-column prop="award_sn" label="奖池期数" width="180">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.awards_cycle}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="用户昵称">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.nickname}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.mobile}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status==0" style="color: red;">未打款</span>
                        <span v-if="scope.row.status==1" style="color: green;">已打款</span>
                        <span v-if="scope.row.status==2" style="color: blue;">已取消</span>
                    </template>
                </el-table-column>

                <el-table-column label="分红金额">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.money}}元</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="发放时间" width="170">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.send_date}}</com-ellipsis>
                    </template>
                </el-table-column>

            </el-table>

            <div style="text-align: center;margin: 20px 0;">
                <el-pagination
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
                keyword: '',
                kw_type: 'mobile',
                listLoading: false,
                page: 1,
                pageCount: 0,
                item_type_options:[
                    {
                        value: 'mobile',
                        label: '手机号'
                    },
                    {
                        value: 'user_id',
                        label: '用户ID'
                    },
                    {
                        value: 'nickname',
                        label: '昵称'
                    },
                ],
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
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
                        r: 'plugin/boss/mall/examine-prize/index',
                        page: self.page,
                        keyword: this.keyword,
                        kw_type: this.kw_type,
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
        }
    });
</script>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>