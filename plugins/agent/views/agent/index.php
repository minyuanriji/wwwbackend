<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:48
 */



Yii::$app->loadPluginComponentView('agent-batch');
Yii::$app->loadPluginComponentView('agent-edit');
Yii::$app->loadPluginComponentView('agent-level');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>经销商列表</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <com-export-dialog :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </com-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <el-select size="small" v-model="search.platform" @change='toSearch' class="select">
                <el-option key="all" label="全部平台" value=""></el-option>
                <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                <el-option key="bdapp" label="百度" value="bdapp"></el-option>
            </el-select>
            <el-select size="small" v-model="search.level" @change='toSearch' class="select">
                <el-option key="all" label="全部等级" value=""></el-option>
                <el-option :key="index" :label="item.name" :value="item.level"
                           v-for="(item, index) in agentLevelList"></el-option>
            </el-select>
            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>
            <div class="batch">
                <agent-batch :choose-list="choose_list" :agent-level-list="agentLevelList"
                           @to-search="loadData"></agent-batch>
            </div>
            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;" @click="editClick">添加经销商
                </el-button>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column prop="user_id" width="80" label="用户ID"></el-table-column>
                    <el-table-column label="基本信息" width="200">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.avatar_url"></com-image>
                            <div>{{scope.row.nickname}}</div>
                            <div>
                                <img v-if="scope.row.userInfo.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                                <img v-if="scope.row.userInfo.platform == 'aliapp'" src="statics/img/mall/ali.png"
                                     alt="">
                                <img v-if="scope.row.userInfo.platform == 'ttapp'" src="statics/img/mall/toutiao.png"
                                     alt="">
                                <img v-if="scope.row.userInfo.platform == 'bdapp'" src="statics/img/mall/baidu.png"
                                     alt="">
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.userInfo.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="累计佣金" prop="total_price">
                        <template slot-scope="scope">
                            <div>{{scope.row.total_price}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="推荐人" prop="parent_name"></el-table-column>
                    <el-table-column width='200' label="下级用户">
                        <template slot-scope="scope">
                            <template v-for="(item, key, index) in share_name" v-if="scope.row[key] !== undefined">
                                <el-button type="text" @click="dialogChildShow(scope.row, index + 1)">
                                    {{item}}：{{scope.row[key]}}
                                </el-button>
                                <br>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="经销商等级" width="120" prop="level">
                        <template slot-scope="scope">
                            <el-tag size="small" type="info" v-if="scope.row.level == 0">默认等级</el-tag>
                            <el-tag size="small" v-else>{{scope.row.level_name}}</el-tag>
                        </template>
                    </el-table-column>
                    </el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>成为经销商时间：<br>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                    </el-table-column>
                    <el-table-column label="备注信息" prop="remarks"></el-table-column>
                    
					<el-table-column
					        label="名额"
					        width="120">
					    <template slot-scope="scope">
							<el-button class="button-item"
								type="primary" @click="levelupSetting(scope.row)" size="small">设置
							</el-button>
					    </template>
					</el-table-column>
					
					<el-table-column label="操作" width="300px">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-top: 10px"
                                       @click.native="order(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="查看订单" placement="top">
                                    <img src="statics/img/mall/agent/order.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="cash(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="提现详情" placement="top">
                                    <img src="statics/img/mall/agent/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="remarks(scope.row)">
                                <el-tooltip class="item" effect="dark" content="添加备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="editLevel(scope.row)">
                                <el-tooltip class="item" effect="dark" content="修改经销商等级" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button circle size="mini" type="text" @click="agentDelete(scope.row, scope.$index)">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tabs>
            <div flex="box:last cross:center">
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
        </div>
    </el-card>
    <el-dialog
            title="下线情况"
            :visible.sync="dialogChild"
            width="40%">
        <div>
            <el-table :data="childList" border v-loading="dialogLoading">
                <el-table-column type="index" label="序号"></el-table-column>
                <el-table-column label="经销商">
                    <template slot-scope="scope">
                        <span>{{select.nickname}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="下线等级" prop="nickname">
                    <template slot-scope="scope">
                        <span v-if="select.status == 1">{{share_name.first}}</span>
                        <span v-if="select.status == 2">{{share_name.second}}</span>
                        <span v-if="select.status == 3">{{share_name.third}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="昵称" prop="nickname"></el-table-column>
                <el-table-column label="成为下线时间" prop="junior_at"></el-table-column>
            </el-table>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogChild = false">取 消</el-button>
            <el-button type="primary" @click="dialogChild = false">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="添加备注" :visible.sync="dialogContent">
        <el-form :model="remarksForm">
            <el-form-item label="备注">
                <el-input type="textagent" v-model="remarksForm.remarks" autocomplete="off"></el-input>
                <el-input style="display: none" :readonly="true" v-model="remarksForm.id"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogContent = false">取 消</el-button>
            <el-button type="primary" @click="remarksSubmit" :loading="remarksLoading">确 定</el-button>
        </div>
    </el-dialog>
	
	<!-- 设置名额 -->
	<el-dialog title="名额设置" :visible.sync="dialogLevelup">
        <el-form>
			<template>
				<el-form-item v-for="(item, index) in levelsList" :label="item.name" style="display: flex;width: 100%;">
					<el-input type="number" :min="0" v-model="item.num" style="max-width: 800px;min-width: 500px;">
						<template slot="append">个</template>
					</el-input>
				</el-form-item>
			</template>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogLevelup = false">取 消</el-button>
            <el-button type="primary" @click="postSetLevel" :loading="remarksLoading">确 定</el-button>
        </div>
    </el-dialog>
	
    <agent-edit v-model="edit.show"></agent-edit>
    <agent-level v-model="level.show" :agent="level.agent" @success="levelSuccess"></agent-level>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            search: {
                keyword: '',
                status: -1,
                page: 1,
                platform: '',
                level: ''
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
            dialogLoading: false,
            childList: [],
            share_name: {
                first: '一级',
                second: '二级',
                third: '三级'
            },
            select: {
                nickname: '',
                status: 'first',
            },
            dialogContent: false,
            remarksForm: {
                remarks: '',
                id: ''
            },
            remarksLoading: false,
            exportList: [],
            edit: {
                show: false,
            },
            level: {
                show: false,
                agent: null,
            },
            agentLevelList: [],
            choose_list: [],
			
			dialogLevelup : false,
			agent_id : '',	//代理商id
			levelsList : [],	//代理商可设置列表
        },
        mounted() {
            this.loadData();
        },
        methods: {

            agentDelete(row, index) {
                let self = this;
                self.$confirm('删除该经销商, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/agent/mall/agent/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
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

            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.nickname;
                alink.click();
            },

            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/agent/mall/agent/index'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                        this.agentLevelList = e.data.data.agentLevelList;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData()
            },
            apply(user_id, status) {
                this.$prompt('请输入原因', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/agent/apply',
                                    user_id: user_id,
                                    status: status,
                                    reason: instance.inputValue
                                },
                                method: 'get'
                            }).then(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                if (e.data.code == 0) {
                                    this.loadData();
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        } else {
                            done();
                        }
                    }
                }).then(({value}) => {
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            },
            order(id) {
                navigateTo({
                    r: 'mall/agent/order',
                    id: id
                })
            },
            cash(user_id) {
                navigateTo({
                    r: 'mall/agent/cash',
                    user_id: user_id
                })
            },

            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            remarks(row) {
                this.dialogContent = true;
                this.remarksForm = {
                    remarks: row.remarks,
                    id: row.id
                }
            },
			
			//2.0 点击名额设置
			levelupSetting(row) {
				this.agent_id = row.id;
				let getData = {agent_id : row.id}
				this.getSetevel(getData);
            },
			// 2.1 获取经销商名额详情
			getSetevel(getData) {
			    this.loading = true;
			    let params = {
			        r: 'plugin/agent/mall/agent/set-level-num'
			    };
			    params = Object.assign(params, getData);
			    request({
			        params: params,
			        method: 'get',
			    }).then(e => {
			        this.loading = false;
			        if (e.data.code == 0) {
						this.dialogLevelup = true;	//显示弹窗
						let levels = e.data.data.levels;
						let setting = e.data.data.setting;
						if(levels.length>0){
							levels.forEach(item=>{
								item['num'] = 0;	//一开始追加0
								if(setting.length>0){
									setting.forEach(its=>{
										// 有相等的就修改值
										item.level==its.level ? item['num'] = its.num : '';
									})
								}
							})
						}
						console.log(levels);
						this.levelsList = JSON.parse(JSON.stringify(levels));
						
			        } else {
			            this.$message.error(e.data.msg);
			        }
			    }).catch(e => {
			        this.loading = false;
			    });
			},
			// 3.0 设置
			postSetLevel(){
				console.log(this.levelsList);
				let levelsList = JSON.parse(JSON.stringify(this.levelsList));
				let setting = {};
				levelsList.forEach((item,index) => {
					let newKey = String(item.level);	//写活key
					setting[newKey] = item.num;
				})
				console.log(setting);
				let postData = {
					form:{
						agent_id : this.agent_id,
						setting : setting,
					}
				}
				this.remarksLoading = true;
				request({
				    params: {
				        r: 'plugin/agent/mall/agent/set-level-num',
				    },
				    method: 'post',
				    data:postData,
				}).then(e => {
				    this.remarksLoading = false;
				    if (e.data.code == 0) {
				        this.dialogLevelup = false;
				        this.$message.success(e.data.msg);
				    } else {
				        this.$message.error(e.data.data.msg);
				    }
				}).catch(e => {
				    this.remarksLoading = false;
				    this.$message.error('未知错误');
				});
			},
			
            remarksSubmit() {
                this.remarksLoading = true;
                request({
                    params: {
                        r: 'plugin/agent/mall/agent/remarks-edit',
                    },
                    method: 'post',
                    data:{
                        remarks: this.remarksForm.remarks,
                        id: this.remarksForm.id
                    }
                }).then(e => {
                    this.remarksLoading = false;
                    if (e.data.code == 0) {
                        this.dialogContent = false;
                        this.loadData();
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.data.msg);
                    }
                }).catch(e => {
                    this.remarksLoading = false;

                    this.$message.error('未知错误');
                });
            },
            dialogChildShow(share, status) {
                this.dialogChild = true;
                this.dialogLoading = true;
                this.select = {
                    nickname: share.nickname,
                    status: status
                };
                request({
                    params: {
                        r: 'mall/agent/team',
                        status: status,
                        id: share.user_id
                    },
                    method: 'get'
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code == 0) {
                        this.childList = e.data.data.list;
                    }
                }).catch(e => {
                    this.dialogLoading = false;
                    this.$message.error('未知错误');
                });
            },
            editClick() {
                this.edit.show = true;
            },
            editLevel(row) {
                this.level.show = true;
                this.level.agent = row;
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
            levelSuccess() {
                this.loadData();
            }
        }
    });
</script>
<style>
    .el-tabs__header {
        font-size: 16px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
        display: inline-block;
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

    .batch {
        margin: 0 0 20px;
        display: inline-block;
    }

    .batch .el-button {
        padding: 9px 15px !important;
    }
	.button-item{
		/* padding: 9px 25px; */
		width: 76px;
		height: 24px;
		text-align: center;
		line-height: 24px;
	}
	
	.el-form-item {
	    /* padding-right: 25%; */
	    /* min-width: 850px; */
	}
	
</style>