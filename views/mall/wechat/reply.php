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
    <el-card class="box-card">
        <div class="header">
            <div class="title">自动回复</div>
        </div>
        <div class="mod-config">
            <el-form :inline="true" :model="dataForm" @keyup.enter.native="getDataList()">
                <el-form-item>
                    <el-input v-model="dataForm.matchValue" placeholder="匹配关键词" clearable></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button @click="getDataList()">查询</el-button>

                    <el-button type="primary" @click="addOrUpdateHandle()">新增</el-button>
                    <el-button type="danger" @click="deleteHandle()" :disabled="dataListSelections.length <= 0">批量删除</el-button>
                </el-form-item>
            </el-form>
            <el-table :data="dataList" border type="expand" v-loading="dataListLoading" @selection-change="selectionChangeHandle" style="width: 100%;">
                <el-table-column type="expand">
                    <template slot-scope="props">
                        <el-form label-position="left" inline class="demo-table-expand">
                            <el-form-item label="ID">
                                <span>{{ props.row.ruleId }}</span>
                            </el-form-item>
                            <el-form-item label="精确匹配">
                                <span>{{ props.row.exactMatch?'是':'否' }}</span>
                            </el-form-item>
                            <el-form-item label="是否有效">
                                <span>{{ props.row.status?'是':'否' }}</span>
                            </el-form-item>
                            <el-form-item label="备注说明">
                                <span>{{ props.row.desc }}</span>
                            </el-form-item>
                            <el-form-item label="生效时间">
                                <span>{{ props.row.effectTimeStart }}</span>
                            </el-form-item>
                            <el-form-item label="失效时间">
                                <span>{{ props.row.effectTimeEnd }}</span>
                            </el-form-item>
                        </el-form>
                    </template>
                </el-table-column>
                <el-table-column type="selection" header-align="center" align="center" width="50">
                </el-table-column>
                <el-table-column prop="ruleName" header-align="center" align="center" show-overflow-tooltip label="规则名称">
                </el-table-column>
                <el-table-column prop="matchValue" header-align="center" align="center" show-overflow-tooltip label="匹配关键词">
                </el-table-column>
                <el-table-column prop="replyType" header-align="center" align="center" :formatter="replyTypeFormat" label="消息类型">
                </el-table-column>
                <el-table-column prop="replyContent" header-align="center" align="center" show-overflow-tooltip label="回复内容">
                </el-table-column>
                <el-table-column fixed="right" header-align="center" align="center" width="150" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" size="small" @click="addOrUpdateHandle(scope.row.ruleId)">修改</el-button>
                        <el-button type="text" size="small" @click="deleteHandle(scope.row.ruleId)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
<!--            <el-pagination @size-change="sizeChangeHandle" @current-change="currentChangeHandle" :current-page="pageIndex" :page-sizes="[10, 20, 50, 100]" :page-size="pageSize" :total="totalCount" layout="total, sizes, prev, pager, next, jumper">-->
<!--            </el-pagination>-->
            <el-pagination background layout="total, prev,pager, next, jumper" :total="10"
                           style="margin-top: 15px;text-align: right;"></el-pagination>
            <!-- 弹窗, 新增 / 修改 -->
<!--            <add-or-update v-if="addOrUpdateVisible" ref="addOrUpdate" @refreshDataList="getDataList"></add-or-update>-->
        </div>
        <el-dialog :title="!dataForm.id ? '新增' : '修改'" :close-on-click-modal="false" :visible.sync="addOrUpdateVisible">
            <el-form :model="dataForm" :rules="dataRule" ref="dataForm" label-width="80px">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="规则名称" prop="ruleName">
                            <el-input v-model="dataForm.ruleName" placeholder="规则名称"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="精确匹配" prop="exactMatch">
                            <el-switch v-model="dataForm.exactMatch" :active-value="true" :inactive-value="false"></el-switch>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="精准匹配词" prop="matchValue">
                    <com-tags-edit v-model="dataForm.matchValue"></com-tags-edit>
                </el-form-item>
                <el-form-item label="包含匹配词" prop="includeValue">
                    <com-tags-edit v-model="dataForm.includeValue"></com-tags-edit>
                </el-form-item>

                <el-row>
                    <el-col :span="12">
                        <el-form-item label="回复类型" prop="replyType">
                            <el-select v-model="dataForm.replyType" @change="onReplyTypeChange">
                                <el-option v-for="(name,key) in KefuMsgType" :key="key" :value="key" :label="name"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="是否启用" prop="status">
                            <el-switch v-model="dataForm.status" :active-value="true" :inactive-value="false"></el-switch>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="生效时间" prop="effectTimeStart">
                            <el-time-picker v-model="dataForm.effectTimeStart" value-format="HH:mm:ss"></el-time-picker>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="失效时间" prop="effectTimeEnd">
                            <el-time-picker v-model="dataForm.effectTimeEnd" value-format="HH:mm:ss"></el-time-picker>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="回复内容" prop="replyContent">
                    <el-input v-model="dataForm.replyContent" type="textarea" :rows="5" placeholder="文本、图文ID、media_id、json配置"></el-input>
                    <el-button type="text" v-show="'text'==dataForm.replyType" @click="addLink">插入链接</el-button>
                </el-form-item>
                <el-form-item label="备注说明" prop="desc">
                    <el-input v-model="dataForm.desc" placeholder="备注说明"></el-input>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
            <el-button @click="addOrUpdateVisible = false">取消</el-button>
            <el-button type="primary" @click="dataFormSubmit()">确定</el-button>
        </span>
        </el-dialog>
    </el-card>
</div>

<script>
 var app = new Vue({
     el: "#app",
     data(){
         return{
             dataList: [],
             pageIndex: 1,
             pageSize: 10,
             totalCount: 0,
             dataListLoading: false,
             dataListSelections: [],
             addOrUpdateVisible: false,
             KefuMsgType: {
                 "text": "文本消息",
                 "image": "图片消息",
                 "voice": "语音消息",
                 "video": "视频消息",
                 "music": "音乐消息",
                 "news": "外链图文消息",
                 "mpnews": "公众号图文消息",
             },
             dataForm: {
                 ruleId: 0,
                 ruleName: "",
                 exactMatch: false,
                 matchValue: "",
                 includeValue:"",
                 replyType: 'text',
                 replyContent: "",
                 status: true,
                 desc: "",
                 effectTimeStart: "00:00:00",
                 effectTimeEnd: "23:59:59"
             },
             dataRule: {
                 ruleName: [
                     { required: true, message: "规则名称不能为空", trigger: "blur" }
                 ],

                 replyType: [
                     { required: true, message: "回复类型（1:文本2:图文3媒体）不能为空", trigger: "blur" }
                 ],
                 replyContent: [
                     { required: true, message: "回复内容不能为空", trigger: "blur" }
                 ],
                 status: [
                     { required: true, message: "是否有效不能为空", trigger: "blur" }
                 ],
                 effectTimeStart: [
                     { required: true, message: "生效起始时间不能为空", trigger: "blur" }
                 ],
                 effectTimeEnd: [
                     { required: true, message: "生效结束时间不能为空", trigger: "blur" }
                 ]
             }
         }
     },
     methods:{
         // 获取数据列表
         getDataList() {
             this.dataListLoading = true
             // this.$http({
             //     url: this.$http.adornUrl('/manage/msgReplyRule/list'),
             //     method: 'get',
             //     params: this.$http.adornParams({
             //         'page': this.pageIndex,
             //         'limit': this.pageSize,
             //         'matchValue': this.dataForm.matchValue,
             //         'sidx': 'rule_id',
             //         'order': 'desc'
             //     })
             // }).then(({ data }) => {
             //     if (data && data.code === 200) {
             //         this.dataList = data.page.list
             //         this.totalCount = data.page.totalCount
             //     } else {
             //         this.dataList = []
             //         this.totalCount = 0
             //     }
                 this.dataListLoading = false
             // })
         },
         // 多选
         selectionChangeHandle(val) {
             this.dataListSelections = val
         },
         // 新增 / 修改
         addOrUpdateHandle(id) {

             if (this.$refs['dataForm']){
                 this.$refs['dataForm'].resetFields();
             }
             this.addOrUpdateVisible = true

             // this.$nextTick(() => {
             //     this.$refs.addOrUpdate.init(id)
             // })
         },
         // 删除
         deleteHandle(id) {
             var ids = id ? [id] : this.dataListSelections.map(item => item.ruleId)
             this.$confirm(`确定对[id=${ids.join(',')}]进行[${id ? '删除' : '批量删除'}]操作?`, '提示', {
                 confirmButtonText: '确定',
                 cancelButtonText: '取消',
                 type: 'warning'
             }).then(() => {
                 // this.$http({
                 //     url: this.$http.adornUrl('/manage/msgReplyRule/delete'),
                 //     method: 'post',
                 //     data: this.$http.adornData(ids, false)
                 // }).then(({ data }) => {
                 //     if (data && data.code === 200) {
                 //         this.$message({
                 //             message: '操作成功',
                 //             type: 'success',
                 //             duration: 1500,
                 //             onClose: () => this.getDataList()
                 //         })
                 //     } else {
                 //         this.$message.error(data.msg)
                 //     }
                 // })

                 console.log("确认删除发送请求")
             })
         },
         replyTypeFormat(row, column, cellValue) {
             return this.KefuMsgType[cellValue];
         },
         // 表单提交
         dataFormSubmit() {
             this.$refs["dataForm"].validate(valid => {
                 if (valid) {
                     console.log("表单验证成功发送请求")
                     this.addOrUpdateVisible = false;
                 }
             });
         },
         addLink() {
             this.dataForm.replyContent += '<a href="链接地址">链接文字</a>'
         },
         onReplyTypeChange(value) {
             console.log(value)
             if ("music" == value) {
                 let demo = { musicurl: "音乐链接", hqmusicurl: "高品质链接", title: "标题", description: "描述", thumb_media_id: "缩略图media_id" }
                 this.dataForm.replyContent = JSON.stringify(demo, null, 4)
             } else {
                 this.dataForm.replyContent = '媒体素材media_id'
             }
         }
     }
 })
</script>

<style>
    .header .title {
        font-size: 16px;
        padding: 0 15px 15px 0;
    }
</style>
