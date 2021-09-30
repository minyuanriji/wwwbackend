<?php
echo $this->render("com-edit");
echo $this->render("com-category");
?>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>应用管理</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="newApp" size="small">添加应用</el-button>
                </div>
            </div>
        </div>

        <div class="table-body">

            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="name" label="应用" width="350">
                    <template slot-scope="scope">
                        <el-row :gutter="10">
                            <el-col :span="6" style="text-align: right">应用名称：</el-col>
                            <el-col :span="18">{{scope.row.name}}</el-col>
                        </el-row>
                        <el-row :gutter="10">
                            <el-col :span="6" style="text-align: right">App Key：</el-col>
                            <el-col :span="18">{{scope.row.app_key}}</el-col>
                        </el-row>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="凭证" width="350">
                    <template slot-scope="scope">
                        <el-row :gutter="10">
                            <el-col :span="6" style="text-align: right">短时凭证：</el-col>
                            <el-col :span="18">******
                                <span v-if="scope.row.is_access_token_expired" style="color: darkred">（已过期）</span>
                                <el-link :href="'<?php echo \yii\helpers\Url::toRoute(["/plugin/alibaba/mall/oauth2/get-token"]);?>&id='+scope.row.id+'&from_page=app/list'" type="primary" icon="el-icon-refresh">刷新</el-link>
                            </el-col>
                        </el-row>
                        <el-row :gutter="10">
                            <el-col :span="6" style="text-align: right">长时凭证：</el-col>
                            <el-col :span="18">******
                                <span v-if="scope.row.is_refresh_token_expired" style="color: darkred">（已过期）</span>
                                <el-link :href="'<?php echo \yii\helpers\Url::toRoute(["/plugin/alibaba/mall/oauth2/refresh-token"]);?>&id='+scope.row.id+'&from_page=app/list'" type="primary" icon="el-icon-refresh">刷新</el-link>
                            </el-col>
                        </el-row>
                    </template>
                </el-table-column>
                <el-table-column prop="type" label="类型" width="200">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 'distribution'" >
                            <div>名称：社交电商</div>
                            <div>类目：{{scope.row.category_num}} <el-link @click="openCategoryDialog(scope.row)" type="primary" icon="el-icon-edit">编辑</el-link></div>
                            <div>商品：{{scope.row.goods_num}} <el-link @click="$navigate({r: 'plugin/alibaba/mall/distribution/goods-list', app_id: scope.row.id})"
                                        type="primary" icon="el-icon-edit">查看</el-link></div>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="更新时间">
                    <template slot-scope="scope">
                        {{scope.row.updated_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <!--
                        <el-button @click="editApp(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteApp(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                        -->
                    </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                    background
                    layout="prev, pager, next"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="float:right;margin:15px"
                    v-if="pagination">
                </el-pagination>
            </el-col>
        </div>
    </el-card>

    <com-edit :visible="editDialogVisible"
              :edit-data="editData"
              @close="close"
              @update="update"></com-edit>


    <com-category :visible="categoryDialogVisible"
              :app-id="categoryAppId"
              @close="categoryDialogVisible = false"></com-category>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                editDialogVisible: false,
                categoryDialogVisible: false,
                categoryAppId: 0,
                editData: {},
                searchData: {
                    keyword: ''
                },
                date: '',
                list: [],
                pagination: null,
                hide_secret: true,
                loading: false
            };
        },
        methods: {
            openCategoryDialog(row){
                this.categoryDialogVisible = true;
                this.categoryAppId = row.id;
            },
            newApp(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            editApp(row){
                this.editData = row;
                this.editDialogVisible = true;
            },
            deleteApp(row){
                let data = {id:row.id};
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/alibaba/mall/app/delete'
                        },
                        method: 'post',
                        data: data
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error("request error");
                    });
                }).catch(() => {

                });
            },
            getList() {
                let params = {
                    r: 'plugin/alibaba/mall/app/list',
                    page: this.page
                };
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
                this.loading = true;
            },
            update(){
                this.getList();
                this.close();
            },
            close(){
                this.editDialogVisible = false;
            }
        },
        mounted: function() {
            this.getList();
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
        width: 250px;
        margin: 0 0 20px 0px;
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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>