<template id="com-category">
    <div class="com-category">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <template v-if="!editMode.is_edit">
                <template v-if="syncStatus">
                    <el-alert title="同步过程中请勿执行任何操作！" type="warning" :closable="false" style="margin-bottom:20px;"> </el-alert>
                    <div style="margin-bottom:20px;">{{syncMessage}}</div>
                </template>

                <!--
                <el-button  @click="doSync" :loading="syncStatus"  type="danger" style="margin-bottom:20px;">{{syncStatus ? "同步中" : "一键同步"}}</el-button>
                -->

                <div style="margin-bottom:20px;" v-if="search.parent.name != ''">
                    <el-tag @close="searchClean" closable>{{search.parent.name}}</el-tag>
                </div>

                <el-table v-if="!syncStatus" :data="list" border style="width: 100%;margin-bottom: 20px;" v-loading="loading">
                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                    <el-table-column prop="scope" width="110" label="排序">
                        <template slot-scope="scope">
                            <el-link type="danger" @click="editSort(scope.row)" v-if="!scope.row.edit_sort">{{scope.row.sort}}<i class="el-icon-edit-outline el-icon--right"></i></el-link>
                            <el-input @blur="saveSort(scope.row)" size="small" v-else v-model="scope.row.sort"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column label="名称" width="260">
                        <template slot-scope="scope">
                            <span v-if="scope.row.ali_parent_id > 0"><span>{{scope.row.name}}</span> 【ID:{{scope.row.ali_cat_id}}】</span>
                            <el-link v-else @click="listChildren(scope.row)" type="primary"><span>{{scope.row.name}}</span> 【ID:{{scope.row.ali_cat_id}}】
                                <i class="el-icon-arrow-right el-icon--right"></i>
                            </el-link>
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
                            <el-button @click="editIt(scope.row)" type="text" circle size="mini">
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

                <div flex="box:last cross:center;">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="list.length > 0"
                                style="display: inline-block;float: right;"
                                background :page-size="pagination.pageSize"
                                @current-change="pageChange"
                                layout="prev, pager, next" :current-page="pagination.current_page"
                                :total="pagination.total_count">
                        </el-pagination>
                    </div>
                </div>
            </template>

            <template v-if="editMode.is_edit">
                <el-form :rules="editMode.rules" ref="formData" label-width="20%" :model="editMode.row" size="small">
                    <el-form-item label="名称" prop="name">
                        <el-input v-model="editMode.row.name" style="width:350px"></el-input>
                    </el-form-item>
                    <el-form-item label="图标" prop="cover_url">
                        <com-attachment :multiple="false" :max="1" v-model="editMode.row.cover_url">
                            <el-tooltip class="item"
                                        effect="dark"
                                        content="建议尺寸:240 * 240"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </com-attachment>
                        <com-image mode="aspectFill" width='80px' height='80px' :src="editMode.row.cover_url"></com-image>
                    </el-form-item>
                    <el-form-item label="排序" prop="sort">
                        <el-input v-model="editMode.row.sort" style="width:200px"></el-input>
                    </el-form-item>
                </el-form>

                <div slot="footer" class="dialog-footer">
                    <el-button @click="editMode.is_edit = false">取 消</el-button>
                    <el-button :loading="editMode.btnLoading" type="primary" @click="editSave">确 定</el-button>
                </div>
            </template>

        </el-dialog>
    </div>
</template>

<script>
    function doPost(vue, r, data, fn){
        request({
            params: {
                r: r
            },
            method: 'post',
            data: data
        }).then(e => {
            if (e.data.code === 0) {
                if(typeof fn == "function"){
                    fn.call(this, vue, e.data);
                }
            } else {
                vue.$message.error(e.data.msg);
            }
        }).catch(e => {
            vue.$message.error("request error");
        });
    }

    Vue.component('com-category', {
        template: '#com-category',
        props: {
            visible: Boolean,
            appId: Number
        },
        data() {
            return {
                syncMessage: "同步中",
                syncStatus: false,
                dialogTitle: "类目管理",
                activeName: "first",
                dialogVisible: false,
                btnLoading: false,
                loading: false,
                list: [],
                pagination: null,
                search:{
                    parent:{ali_cat_id:0, name:''}
                },
                editMode: {
                    is_edit: false,
                    btnLoading: false,
                    row: {name:'', cover_url: '', sort:0},
                    rules:{
                        name: [
                            {required: true, message: '名称不能为空', trigger: 'change'},
                        ],
                        cover_url: [
                            {required: true, message: '头像不能为空', trigger: 'change'},
                        ],
                        sort: [
                            {required: true, message: '排序不能为空', trigger: 'change'},
                        ]
                    }
                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
                if(this.dialogVisible){
                    this.getList();
                }
            }
        },
        methods: {
            editSave(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.editMode.btnLoading = true;
                        doPost(that, 'plugin/alibaba/mall/distribution/edit-category', that.editMode.row, function (vue, res){
                            vue.editMode.btnLoading = false;
                            vue.editMode.is_edit = false;
                            vue.$message.success(res.msg);
                        });
                    }
                });
            },
            editIt(row){
                this.editMode.is_edit = true;
                this.editMode.row = row;
            },
            deleteIt(row){
                let data = {id:row.id};
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    doPost(this, 'plugin/alibaba/mall/distribution/delete-category', data, function(vue, res){
                        vue.$message.success(res.msg);
                        vue.getList();
                    });
                }).catch(() => {

                });
            },
            editSort(row){
                row['edit_sort'] = true;
            },
            saveSort(row){
                row['edit_sort'] = false;
                let data = {
                    id:row.id,
                    sort:row.sort
                };
                doPost(this, 'plugin/alibaba/mall/distribution/edit-category-sort', data, function(vue, res){
                    vue.$message.success(res.msg);
                });
            },
            doSync(){
                this.$confirm('你确定要执行一键同步操作吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.syncStatus = true;
                    this.doSyncNext({});
                }).catch(() => {

                });
            },
            doSyncNext(data){
                data['app_id'] = this.appId;
                doPost(this, 'plugin/alibaba/mall/distribution/sync-category', data, function (vue, res){
                    vue.syncMessage = res.data.content;
                    if(res.data.is_finished == 1){
                        vue.syncStatus = false;
                        vue.getList();
                    }else{
                        vue.doSyncNext(res.data);
                    }
                });
            },
            listChildren(parent){
                this.page = 1;
                this.search.parent = parent;
                this.getList();
            },
            searchClean(){
                this.page = 1;
                this.search.parent.name = '';
                this.search.parent.ali_cat_id = 0;
                this.getList();
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                let params = {
                    r: 'plugin/alibaba/mall/distribution/category-list',
                    page: this.page,
                    parent_id:this.search.parent.ali_cat_id
                };
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        for(var i=0; i < e.data.data.list.length; i++){
                            e.data.data.list[i]['edit_sort'] = false;
                        }
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
            close(){
                this.editMode.is_edit = false;
                this.$emit('close');
            }
        }
    });
</script>