<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: huangpan
 * Date: 2020-04-21
 * Time: 15:03
 */
Yii::$app->loadComponentView('com-rich-text')
?>

<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" shadow="never"
             style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>图文管理</span>
            <div style="float: right; margin: -5px 0">
                <el-form :inline="true" :model="ruleForm">
                    <el-button type="primary" size="small" @click="addOrUpdateHandle()">新增</el-button>
                </el-form>
            </div>
        </div>
        <div class="table-body">
            <el-table :data="list" border style="width: 100%;">
                <el-table-column prop="id" header-align="center" align="center" label='ID'>
                </el-table-column>
                <el-table-column prop="media_id" header-align="center" align="left" label="素材mediaId">
                </el-table-column>
                <el-table-column prop="title" header-align="center" align="center" label="标题">
                </el-table-column>
                <el-table-column header-align="center" align="center" label="封面图">
                    <template slot-scope="scope">
                        <el-image style="width: 50px; height: 50px" :src="scope.row.cover_pic" fit="fit" :preview-src-list="[scope.row.cover_pic]"></el-image>
                    </template>
                </el-table-column>
                <el-table-column prop="updated_at" header-align="center" align="center" label="更新时间">
                </el-table-column>
                <el-table-column fixed="right" header-align="center" align="center" width="150" label="操作">
                    <template slot-scope="scope">
                        <el-button type="danger" size="mini" @click="deleteArticle(scope.row.id,scope.row.title)">删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        background
                        @current-change="pagination"
                        layout="total, prev,pager, next, jumper"
                        :total="totalCount"
                        :page-count="pageCount">
                </el-pagination>
            </div>
        </div>

        <el-dialog :title="!ruleForm.id ? '新增' : '修改'" :close-on-click-modal="false" :visible.sync="addOrUpdateVisible">
            <el-form :model="ruleForm" :rules="dataRule" ref="ruleForm" label-width="120px">
                <el-form-item label="标题" prop="title">
                    <el-input v-model="ruleForm.title" placeholder="标题"></el-input>
                </el-form-item>
                <el-form-item label="作者" prop="author">
                    <el-input v-model="ruleForm.author" placeholder="作者"></el-input>
                </el-form-item>
                <el-form-item label="链接" prop="url">
                    <el-input v-model="ruleForm.url" placeholder="链接"></el-input>
                </el-form-item>
                <el-form-item label="阅读原文链接" prop="source_url">
                    <el-input v-model="ruleForm.source_url" placeholder="阅读原文链接"></el-input>
                </el-form-item>
                <el-form-item label="描述" prop="article_desc">
                    <el-input v-model="ruleForm.article_desc" placeholder="描述"></el-input>
                </el-form-item>
                <el-form-item label="封面图片" prop="thumb_media_id">
                    <!--                <el-form-item label="media_id" prop="thumb_media_id">-->
                    <!--                    <el-input v-model="ruleForm.thumb_media_id" placeholder="封面图media_id"></el-input>-->
                    <el-button type="primary">
                        上传封面图
                        <input type="file" accept="image/jpeg,image/jpg,image/png,image"
                               style="opacity: 0;height: 100%;position: absolute;left: 0;top: 0;"
                               @change="onFileChange"/>
                    </el-button>
                </el-form-item>
                <el-form-item v-if="imageData.url">
                    <el-image :src="imageData.url" style="width: 200px; height: 200px"></el-image>
                </el-form-item>
                <el-form-item label="内容" prop="content">
                    <!--                    <tinymce-editor ref="editor" v-model="ruleForm.content"></tinymce-editor>-->
                    <com-rich-text v-model="ruleForm.content"></com-rich-text>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="addOrUpdateVisible=false">取消</el-button>
                <el-button type="primary" @click="ruleFormSubmit()">确定</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>

<script>
    var app = new Vue({
        el: "#app",
        data() {
            return {
                addOrUpdateVisible: false,
                dataList: [],
                pageIndex: 1,
                pageSize: 20,
                ruleForm: {
                    templateId: 0,
                    title: '',
                    content: '',
                    showCoverPic: true,
                    url: '',
                    author: '',
                    source_url: '',
                    article_desc: '',
                    thumb_media_id: '',
                    media_type: 'rich-text',
                    cover_pic: '',
                    media_id: ''
                },
                list: [],
                listLoading: false,
                page: 1,
                totalCount: 0,
                pageCount: 0,
                imageData: {
                    file: null,
                    url: ''
                },
                dataRule: {
                    title: [
                        {required: true, message: '标题不能为空', trigger: 'blur'}
                    ],
                    content: [
                        {required: true, message: '内容不能为空', trigger: 'blur'}
                    ],
                    thumb_media_id: [
                        {required: true, message: '封面图media_id不能为空', trigger: 'blur'}
                    ]
                }
            }
        },
        methods: {
            //分页
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
                        r: 'mall/wechat/article',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.totalCount = e.data.data.pagination.total_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            //删除
            deleteArticle(id, title) {
                let self = this;
                this.$confirm(`确定对[${title}]进行删除操作?`, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/wechat/article-delete'
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
            // 当前页
            currentChangeHandle(val) {
                this.page = val
                this.getList()
            },
            // 新增 / 修改
            addOrUpdateHandle(id) {

                if (this.$refs['ruleForm']) {
                    this.$refs['ruleForm'].resetFields();
                }
                id = id || '';
                this.addOrUpdateVisible = true
                this.ruleForm.id = id;
            },
            ruleFormSubmit() {
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        let self = this;
                        if (valid) {
                            self.btnLoading = true;
                            request({
                                params: {
                                    r: 'mall/wechat/article'
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
            // 当前页
            currentChangeHandle(val) {
                this.pageIndex = val
                this.getDataList()
            },
            // 即时上传图文的封面
            onFileChange(e) {
                let file = event.currentTarget.files[0]
                this.imageData.file = file;
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
                params['r'] = 'mall/wechat/upload-cover'
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
                        self.ruleForm.thumb_media_id = e.data.data.media_id;
                        self.imageData.url = e.data.data.thumb_url;
                        self.ruleForm.cover_pic = e.data.data.thumb_url;
                    }
                }).catch(e => {

                });


            },
        },
        mounted: function () {
            this.getList();
        }

    })
</script>

<style>
    .header .title {
        font-size: 16px;
        padding: 0 15px 15px 0;
    }
</style>