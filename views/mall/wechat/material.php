<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: huangpan
 * Date: 2020-04-21
 * Time: 15:03
 */
?>

<div id="app" v-cloak>

    <el-card v-loading="listLoading" class="box-card" shadow="never"
             style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>素材管理</span>
            <div style="float: right; margin: -5px 0">
                <el-form :inline="true" :model="ruleForm">
                        <el-button type="primary" size="small" @click="addOrUpdateHandle()">新增</el-button>
                </el-form>
            </div>
        </div>
        <div class="table-body">
            <el-table :data="list" border  style="width: 100%;">
                <el-table-column prop="id" header-align="center" align="center" label="素材ID">
                </el-table-column>
                <el-table-column prop="media_id" header-align="center" align="center" label="素材mediaId">
                </el-table-column>
                <el-table-column prop="name" header-align="center" align="center" label="素材名称">
                </el-table-column>
                <el-table-column header-align="center" align="center" label="媒体类型">
                    <template slot-scope="scope">
                        {{scope.row.media_type}}
                    </template>
                </el-table-column>
                <el-table-column prop="updated_at" header-align="center" align="center" label="更新时间">
                </el-table-column>
                <el-table-column fixed="right" header-align="center" align="center" width="150" label="操作">
                    <template slot-scope="scope">

                        <el-button type="danger" size="small" @click="deleteHandle(scope.row.id,scope.row.name)">删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                    background
                    @current-change="pagination"
                    :total="pagination.total_count"
                    layout="total, prev,pager, next, jumper"
                    :page-count="pageCount" style="margin-top: 15px;text-align: right;">
            </el-pagination>

        </div>

        <!-- 弹窗, 新增 / 修改 -->
        <el-dialog :title="!ruleForm.id ? '新增' : '修改'" :close-on-click-modal="false" :visible.sync="addOrUpdateVisible">
            <el-form :model="ruleForm" :rules="dataRule" ref="ruleForm" @keyup.enter.native="ruleFormSubmit()"
                     label-width="80px">
                <el-row>
                    <el-col :span="12">

                        <el-form-item label="媒体文件">
                            <el-button type="primary" size="mini">
                                选择文件
                                <input type="file" style="opacity: 0;height: 100%;position: absolute;left: 0;top: 0;"
                                       @change="onFileChange"/>
                            </el-button>
                            <div>{{ruleForm.name}}</div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="媒体类型" prop="media_type">
                            <el-select v-model="ruleForm.media_type" placeholder="媒体类型" style="width:100%">
                                <el-option label="图片" value="image"></el-option>
                                <el-option label="视频" value="video"></el-option>
                                <el-option label="语音" value="voice"></el-option>
                                <el-option label="缩略图（64K以内JPG）" value="thumb"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="素材名称" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="素材名称"></el-input>
                </el-form-item>
                <el-form-item label="素材描述" prop="material_desc">
                    <el-input v-model="ruleForm.material_desc" placeholder="素材描述"
                              :disabled="ruleForm.media_type!=='video'"></el-input>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
            <el-button @click="addOrUpdateVisible = false">取消</el-button>
            <el-button type="primary" @click="ruleFormSubmit()" :loading="btnLoading">确定</el-button>
        </span>
        </el-dialog>
    </el-card>

</div>

<script>
    var app = new Vue({
        el: "#app",
        data() {
            return {
                files: [],
                addOrUpdateVisible: false,
                dataList: [],
                pageIndex: 1,
                pageSize: 20,
                totalCount: 0,
                isNotVideo: true,
                dataListLoading: false,
                btnLoading: false,
                ruleForm: {
                    id: '',
                    file: '',
                    name: '',
                    media_type: 'image',
                    file_path: '',
                    material_desc: ''
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                dataRule: {
                    name: [
                        {required: true, message: '素材名称不能为空', trigger: 'blur'}
                    ],
                    media_type: [
                        {required: true, message: '素材类型不能为空', trigger: 'blur'}
                    ]
                }
            }
        },
        watch: {},
        methods: {
            deleteHandle(id, title) {
                let self = this;
                this.$confirm(`确定对[${title}]进行删除操作?`, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/wechat/material-delete'
                            },
                            method: 'post',
                            data: {
                                form: {
                                    id: id
                                },
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                                self.getList();

                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {

                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    }
                )


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
                        r: 'mall/wechat/material',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.pagination = e.data.data.pagination;
                }).catch(e => {
                    console.log(e);
                });
            },
            fileResult(e) {
                if (e.length) {
                    this.ruleForm.file_path = e.url;
                }
            },
            ruleFormSubmit() {
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        let self = this;
                        if (valid) {
                            self.btnLoading = true;
                            if (self.ruleForm.type == 'video') {
                                if (self.ruleForm.material_desc == '') {
                                    self.$message.error('请填写视频描述');
                                    return;
                                }
                            }
                            request({
                                params: {
                                    r: 'mall/wechat/material'
                                },
                                method: 'post',
                                data: {
                                    form: self.ruleForm,
                                }
                            }).then(e => {
                                self.btnLoading = false;
                                if (e.data.code === 0) {
                                    self.$message.success(e.data.msg);
                                    self.addOrUpdateVisible = false;

                                } else {
                                    self.$message.error(e.data.msg);
                                }
                            }).catch(e => {

                                self.$message.error(e.data.msg);
                                self.btnLoading = false;
                            });
                        } else {
                            console.log('error submit!!');
                            return false;
                        }

                    }
                })
            },
            // 新增 / 修改
            addOrUpdateHandle(id) {
                if (this.$refs['ruleForm']) {
                    this.$refs['ruleForm'].resetFields();
                    this.ruleForm.file = '';
                }
                this.addOrUpdateVisible = true
                this.ruleForm.id = id;
            },
            onFileChange(e) {
                let file = event.currentTarget.files[0]
                if (!file) return;
                this.ruleForm.file = file;
                this.ruleForm.name = file.name.substring(0, file.name.lastIndexOf('.'))
                let media_type = file.type.substring(0, file.type.lastIndexOf('/'))
                if (media_type == 'audio') media_type = 'voice'
                this.ruleForm.media_type = media_type
                this.uploadFiles(e.target.files)
            },
            uploadFiles(rawFiles) {
                if (this.max && rawFiles.length > this.max) {
                    this.$message.error('最多一次只能上传' + this.max + '个文件。')
                    return
                }
                this.files = []
                for (let i = 0; i < rawFiles.length; i++) {
                    const file = {
                        _complete: false,
                        response: null,
                        rawFile: rawFiles[i]
                    }
                    this.files.push(file)
                }
                this.$emit('start', this.files)
                for (const i in this.files) {
                    this.upload(this.files[i])
                }
            },
            upload(file) {
                let self = this;
                const formData = new FormData()
                const params = {}
                params['r'] = 'mall/wechat/material-upload'
                for (const i in this.params) {
                    params[i] = this.params[i]
                }
                for (const i in this.fields) {
                    formData.append(i, this.fields[i])
                }
                formData.append('file', file.rawFile, file.rawFile.name)
                this.$request({
                    headers: {"Content-Type": "multipart/form-data"},
                    params: params,
                    method: "post",
                    data: formData
                }).then(e => {
                    if (e.data.code === 1) {
                        self.$message.error(e.data.msg);
                    }
                    if (e.data.code === 0) {
                        self.ruleForm.file_path = e.data.data.url;
                    }
                }).catch(e => {

                });
            },
        },
        mounted: function () {

            this.getList();
        },
    })
</script>

<style>
    .header .title {
        font-size: 16px;
        padding: 0 15px 15px 0;
    }
</style>