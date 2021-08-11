<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-07
 * Time: 16:22
 */
?>


<div id="app" v-cloak>
	<!-- <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
	    <div slot="header">
	        <div>
                <el-button :type="pageName=='plan' ? 'primary' : '' "  @click="$navigate('mall/finance/integral-plan')">红包券发放计划</el-button>
	        </div>
	    </div>
	</el-card> -->
	
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>红包券发放计划</span>
            </div>
        </div>
		
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="60"></el-table-column>
                <el-table-column label="会员信息" width="200">
                    <template slot-scope="scope">
                        <com-image style="float: left;margin-right: 5px;" mode="aspectFill" :src="scope.row.user.avatar_url"></com-image>
                        <div v-if=scope.row.user.nickname>{{scope.row.user.nickname}}</div>
                        <div v-else>{{scope.row.user.username}}</div>
                        <div>
                            <img v-if="scope.row.user.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                            <img v-if="scope.row.user.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                            <img v-if="scope.row.user.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                            <img v-if="scope.row.user.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="integral_num" label="红包券数量"></el-table-column>
                <el-table-column  label="红包券类型">
                    <template slot-scope="scope">
                            <div size="small">{{getTypeName(scope.row.type)}}</div>
                        </template>
                </el-table-column>
                <el-table-column label="周期">
				    <template slot-scope="scope">
				        <div size="small">{{scope.row.period}}{{scope.row.period_unit=="month"?'月':'周'}}</div>
					</template>
				</el-table-column>
                </el-table-column>
                <el-table-column label="已发放周期">
                    <template slot-scope="scope">
				        <div size="small">{{scope.row.finish_period}}{{scope.row.period_unit=="month"?'月':'周'}}</div>
					</template>
                </el-table-column>
                <el-table-column label="状态">
				    <template slot-scope="scope">
				        <div size="small">{{getStatusName(scope.row.status)}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="desc" label="描述"></el-table-column>
                <el-table-column  label="下次发放时间">
                    <template slot-scope="scope">
                        <div size="small" v-if="scope.row.next_publish_time > 0">{{scope.row.next_publish_time|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        <div size="small" v-if="scope.row.next_publish_time == 0">--</div>
                    </template>
                </el-table-column>
                
                
            </el-table>
            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :total="pagination.totalCount*1"
                        style="float:right;margin:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
				pageName : 'plan',
                keyword: '',
                form: [],
                pagination: null,
                listLoading: false,
            };
        },
        methods: {
			// 0.1 tab点击跳转页面
			toOtherPage(url){
				this.$navigate({
				    r: url,
				    id: row.id,
				    pic_url: null
				});
			},
            getStatusName(value) {
                if(this.status_list) return this.status_list[value];
            },
            getTypeName(value) {
                if(this.type_list) return this.type_list[value];
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.start_date = this.date[0];
                this.searchData.end_date = this.date[1];
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.date = ''
                }
                this.getList();
            },
            getList() {
                let params = {
                    r: 'mall/finance/integral-plan',
                    page: this.page,
                    keyword: this.keyword,
                };
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.plan.list;
                        this.status_list = e.data.data.status_list;
						this.type_list = e.data.data.type_list;
                        this.pagination = e.data.data.plan.pagination;
                        console.log(this.pagination);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
            },
        },
        mounted: function () {
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
        margin: 0 0 20px 20px;
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