<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: huangpan
 * Date: 2020-04-21
 * Time: 19:32
 */

Yii::$app->loadComponentView('wechat/com-tags-edit');
?>

<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" shadow="never"
             style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>自动回复</span>
            <div style="float: right; margin: -5px 0">
                <el-form :inline="true" :model="ruleForm" @keyup.enter.native="getlist()">
                    <!--     <el-form-item>
                             <el-input v-model="keyword" placeholder="匹配关键词" clearable></el-input>
                         </el-form-item>-->

                    <!-- <el-button size="small" @click="search">查询</el-button>-->
                    <el-button type="primary" size="small" @click="addOrUpdateHandle()">新增</el-button>
                </el-form>
            </div>
        </div>
        <div class="table-body">
            <el-table :data="list" border type="expand" v-loading="listLoading"
                      @selection-change="selectionChangeHandle" style="width: 100%;">
                <el-table-column type="expand">
                    <template slot-scope="props">
                        <el-form label-position="left" inline class="demo-table-expand">
                            <el-form-item label="ID">
                                <span>{{ props.row.id }}</span>
                            </el-form-item>
                            <el-form-item label="精确匹配">
                                <span>{{ props.row.type?'是':'否' }}</span>
                            </el-form-item>
                            <el-form-item label="是否有效">
                                <span>{{ props.row.status?'是':'否' }}</span>
                            </el-form-item>
                        </el-form>
                    </template>
                </el-table-column>

                <el-table-column prop="id" header-align="center" align="center" show-overflow-tooltip
                                 label="ID">
                </el-table-column>
                <el-table-column prop="name" header-align="center" align="center" show-overflow-tooltip
                                 label="规则名称">
                </el-table-column>
                <el-table-column header-align="center" align="center" show-overflow-tooltip
                                 label="匹配关键词">
                    <template slot-scope="scope">
                        {{scope.row.keywords|ArrToString}}
                    </template>


                </el-table-column>
                <el-table-column prop="reply_type" header-align="center" align="center" :formatter="reply_typeFormat"
                                 label="消息类型">
                </el-table-column>
                <el-table-column prop="content" header-align="center" align="center" show-overflow-tooltip
                                 label="回复内容">
                </el-table-column>
                <el-table-column fixed="right" header-align="center" align="center" width="150" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" size="small" @click="addOrUpdateHandle(scope.row)">修改</el-button>
                        <el-button type="text" size="small" @click="deleteHandle(scope.row.id,scope.row.name)">删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <!--            <el-pagination @size-change="sizeChangeHandle" @current-change="currentChangeHandle" :current-page="pageIndex" :page-sizes="[10, 20, 50, 100]" :page-size="pageSize" :total="totalCount" layout="total, sizes, prev, pager, next, jumper">-->
            <!--            </el-pagination>-->
            <el-pagination
                    background
                    @current-change="pagination"
                    :total="totalCount"
                    layout="total, prev,pager, next, jumper"
                    :page-count="pageCount" style="margin-top: 15px;text-align: right;">
            </el-pagination>

            <!-- 弹窗, 新增 / 修改 -->
            <!--            <add-or-update v-if="addOrUpdateVisible" ref="addOrUpdate" @refreshlist="getlist"></add-or-update>-->
        </div>
        <el-dialog :title="!ruleForm.id ? '新增' : '修改'" :close-on-click-modal="false" :visible.sync="addOrUpdateVisible">
            <el-form :model="ruleForm" :rules="dataRule" ref="ruleForm" label-width="80px">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="规则名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="规则名称"></el-input>
                        </el-form-item>
                    </el-col>

                </el-row>
                <el-form-item label="精准匹配" prop="matchKeywords">
                    <com-tags-edit v-model="ruleForm.matchKeywords"></com-tags-edit>
                </el-form-item>
                <el-form-item label="包含匹配" prop="includeKeywords">
                    <com-tags-edit v-model="ruleForm.includeKeywords"></com-tags-edit>
                </el-form-item>


                <el-row>
                    <el-col :span="12">
                        <el-form-item label="回复类型" prop="reply_type">
                            <el-select v-model="ruleForm.reply_type" @change="onReplyTypeChange">
                                <el-option v-for="(name,key) in KefuMsgType" :key="key" :value="key"
                                           :label="name"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="是否启用" prop="status">
                            <el-switch v-model="ruleForm.status" :active-value="1"
                                       :inactive-value="0"></el-switch>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-form-item label="回复内容" prop="content">
                    <el-input v-model="ruleForm.content" type="textarea" :rows="5"
                              placeholder="文本、图文ID、media_id、json配置"></el-input>
                    <el-button type="text" v-show="'text'==ruleForm.reply_type" @click="addLink">插入链接</el-button>
                </el-form-item>

            </el-form>
            <span slot="footer" class="dialog-footer">
            <el-button @click="addOrUpdateVisible = false">取消</el-button>
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
                list: [],
                pageIndex: 1,
                pageSize: 10,
                totalCount: 0,
                pageCount: 0,
                keyword: '',//搜索用的关键词
                listLoading: false,
                listSelections: [],
                addOrUpdateVisible: false,
                KefuMsgType: {
                    "text": "文本消息",
                    "image": "图片消息",
                    "voice": "语音消息",
                    "video": "视频消息",
                    "article": "公众号图文消息",
                },
                ruleForm: {
                    id: 0,
                    name: "",
                    matchKeywords: "",
                    includeKeywords: "",
                    reply_type: 'text',
                    content: "",
                    status: 0,
                },
                dataRule: {
                    name: [
                        {required: true, message: "规则名称不能为空", trigger: "blur"}
                    ],

                    reply_type: [
                        {required: true, message: "回复类型（1:文本2:图文3媒体）不能为空", trigger: "blur"}
                    ],
                    content: [
                        {required: true, message: "回复内容不能为空", trigger: "blur"}
                    ],
                    status: [
                        {required: true, message: "是否有效不能为空", trigger: "blur"}
                    ],
                    effectTimeStart: [
                        {required: true, message: "生效起始时间不能为空", trigger: "blur"}
                    ],
                    effectTimeEnd: [
                        {required: true, message: "生效结束时间不能为空", trigger: "blur"}
                    ]
                }
            }
        },
        filters: {
            ArrToString(value) {
                if (!value.length) {
                    return '无关键词';
                }
                let val = [];
                value.forEach(v => {
                    val.push(v.keyword);
                })
                return val.join(',');
            }
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
                        r: 'mall/wechat/reply-rule',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    // self.totalCount = e.data.data.pagination.totalCount;
                    self.totalCount = Number(e.data.data.pagination.totalCount);
                    self.pagination = e.data.data.pagination;
                }).catch(e => {
                    console.log(e);
                });
            },
            search() {
                this.page = 1;
                this.getList();
            },
            // 多选
            selectionChangeHandle(val) {
                this.listSelections = val
            },
            // 新增 / 修改
            addOrUpdateHandle(row) {
                this.addOrUpdateVisible = true

                if (row) {
                    this.ruleForm.id = row.id;
                    this.ruleForm.name = row.name;
                    this.ruleForm.status = parseInt(row.status);
                    this.ruleForm.content = row.content;
                    this.ruleForm.reply_type = row.reply_type;
                    let val = [];
                    this.ruleForm.includeKeywords = row.include_keywords;
                    row.include_keywords.forEach(v => {
                        val.push(v);
                    })
                    this.ruleForm.includeKeywords = val.join(',');
                    val = [];
                    row.match_keywords.forEach(v => {
                        val.push(v);
                    })
                    this.ruleForm.matchKeywords = val.join(',');
                }
            },
            // 删除
            deleteHandle(id, name) {
                let self = this;
                this.$confirm(`确定对[${name}]进行删除操作?`, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/wechat/reply-delete'
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
                })
            },
            reply_typeFormat(row, column, cellValue) {
                return this.KefuMsgType[cellValue];
            },
            // 表单提交
            ruleFormSubmit() {
                this.$refs["ruleForm"].validate(valid => {
                    if (valid) {
                        this.$refs['ruleForm'].validate((valid) => {
                            if (valid) {
                                let self = this;
                                if (valid) {
                                    self.btnLoading = true;
                                    request({
                                        params: {
                                            r: 'mall/wechat/reply-rule'
                                        },
                                        method: 'post',
                                        data: {
                                            form: {
                                                id:self.ruleForm.id,
                                                match_keywords: self.ruleForm.matchKeywords,
                                                include_keywords: self.ruleForm.includeKeywords,
                                                name: self.ruleForm.name,
                                                type: self.ruleForm.type,
                                                reply_type: self.ruleForm.reply_type,
                                                content: self.ruleForm.content,
                                                status: self.ruleForm.status ? 1 : 0
                                            },
                                        }
                                    }).then(e => {
                                        self.btnLoading = false;
                                        if (e.data.code === 0) {
                                            self.$message.success(e.data.msg);
                                            self.addOrUpdateVisible = false;
                                            self.getList();
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


                        //   this.addOrUpdateVisible = false;
                    }
                });
            },
            addLink() {
                this.ruleForm.content += '<a href="链接地址">链接文字</a>'
            },
            onReplyTypeChange(value) {
                console.log(value)
                if ("music" == value) {
                    let demo = {
                        musicurl: "音乐链接",
                        hqmusicurl: "高品质链接",
                        title: "标题",
                        description: "描述",
                        thumb_media_id: "缩略图media_id"
                    }
                    this.ruleForm.content = JSON.stringify(demo, null, 4)
                } else {
                    this.ruleForm.content = '媒体素材media_id'
                }
            }
        },
        mounted() {
            this.getList()
        }
    })
</script>
