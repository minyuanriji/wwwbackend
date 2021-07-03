<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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

</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>积分卡券</span>
                <div style="float: right; margin: -5px 0">
                    <el-button type="primary" @click="edit" size="small">新建卡券</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
			<div class="input-item">
			    <el-input @keyup.enter.native="search" size="small"
			              placeholder="请输入绑定用户搜索"
			              v-model="keyword"
			              clearable
			              @clear="search">
			        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
			    </el-input>
			</div>
			
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column prop="name" label="卡券名称" width="200"></el-table-column>
                <el-table-column prop="user.nickname" label="绑定用户"></el-table-column>
                <el-table-column label="积分类型" prop="integral_setting">
                    <template slot-scope="scope">
						<div size="small">{{scope.row.integral_setting.expire==-1?'永久有效':'限时有效'}}</div>
					</template>
                </el-table-column>
				<el-table-column label="卡券面值" prop="integral_setting">
				    <template slot-scope="scope">
				        <div size="small">{{scope.row.integral_setting.integral_num}}</div>
					</template>
				</el-table-column>
				<el-table-column label="发放周期" prop="integral_setting" width="160">
				    <template slot-scope="scope">
				        <div size="small">{{scope.row.integral_setting.period}}{{scope.row.integral_setting.period_unit=="month"?'月':'周'}}</div>
					</template>
				</el-table-column>
				<el-table-column label="过期天数" prop="integral_setting">
				    <template slot-scope="scope">
				        <div size="small" v-if="scope.row.integral_setting.expire!=-1">{{scope.row.integral_setting.expire}}天</div>
				    </template>
				</el-table-column>
				
				
                <el-table-column prop="expire_time" label="有效期" width="220">
					<template slot-scope="scope">
					    <div size="small">{{scope.row.expire_time|formatDate}}</div>
					</template>
				</el-table-column>
                <el-table-column prop="generate_num" label="生成张数"></el-table-column>
                <el-table-column prop="use_num" label="领取张数"></el-table-column>
                <el-table-column prop="fee" label="手续费">
					<template slot-scope="scope">
					    <div size="small">{{scope.row.fee}}元</div>
					</template>
				</el-table-column>
                <el-table-column prop="updated_at" label="生成时间" width="220">
					<template slot-scope="scope">
					    <div size="small">{{scope.row.updated_at|formatDate}}</div>
					</template>
				</el-table-column>
				<el-table-column prop="status" label="状态">
					<template slot-scope="scope">
					    <div size="small">{{scope.row.status==1?'已生成':'未生成'}}</div>
					</template>
				</el-table-column>
				
                <el-table-column fixed="right" label="操作" width="200">
                    <template slot-scope="scope" >
                        <el-button v-if="scope.row.status==1" size="mini" @click="toLook(scope.row.id)">查看</el-button>
                        <el-button v-if="scope.row.status==0" size="mini" @click="generateCard(scope.row.id)">生成卡片 </el-button>
                        <el-button v-if="scope.row.status==0" size="mini" @click="edit(scope.row.id)">编辑</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
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
				status:0,
                list: [],
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0,
            };
        },
		filters: {
		  // 秒级时间戳转标准时间格式
		  formatDate: function (value) {
			let date = new Date(value*1000);
			let y = date.getFullYear();
			let MM = date.getMonth() + 1;
			MM = MM < 10 ? ('0' + MM) : MM;
			let d = date.getDate();
			d = d < 10 ? ('0' + d) : d;
			let h = date.getHours();
			h = h < 10 ? ('0' + h) : h;
			let m = date.getMinutes();
			m = m < 10 ? ('0' + m) : m;
			let s = date.getSeconds();
			s = s < 10 ? ('0' + s) : s;
			return y + '-' + MM + '-' + d + ' ' + h + ':' + m + ':' + s;
		  }
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
			
			// 0.1 获取卡券列表
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/integral_card/admin/card/index',
                        page: self.page,
                        keyword: this.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
			// 新建或编辑
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/integral_card/admin/card/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/integral_card/admin/card/edit',
                    });
                }
            },
			// 查看不可编辑
			toLook(id) {
				navigateTo({
					r: 'plugin/integral_card/admin/card/card-list',
					id: id,
				});
            },
			// 生成卡片--这里是发请求
			generateCard(id){
				request({
				    params: {
				        r: 'plugin/integral_card/admin/card/generate-card',
				    },
				    data: {card_id:id},
				    method: 'post'
				}).then(e => {
				    if (e.data.code === 0) {
						let self = this;
						this.$message.success(e.data.msg);
						// 修改对应item的状态
						self.list.forEach((item, index) => {
							if(item.id==id){
								self.$set(self.list[index], 'status', 1);
							}
						});
				    } else {
				        this.$message.error(e.data.msg);
				    }
				    this.btnLoading = false;
				}).catch(e => {
				    this.btnLoading = false;
				});
			},
			
			
			
            switchStatus(row) {
                let self = this;
                console.log(row.id);
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/integral_card/admin/profit-level/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.getList();
                }).catch(e => {
                    console.log(e);
                });
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该用户卡券, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/integral_card/admin/profit-level/destroy',
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
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
