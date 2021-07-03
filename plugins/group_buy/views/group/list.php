<?php
// 引入当前搜索组件
Yii::$app->loadPluginComponentView('group-list-search');
?>	

<!-- 这个是拼团列表 -->
<div id="app">
    <el-card class="box-card">
        <div slot="header" class="clearfix">
            <span>开团列表</span>
        </div>
		
		<com-search
				@search="toSearch"
				:is-show-order-type="true"
				:is-show-order-plugin="true"
				>
		</com-search>
		
    </el-card>
	
	
	
    <div class="table-body">
    <template>
        <el-table
                :data="list"
                border
                style="width: 100%">
            <el-table-column
                    fixed
					label="开团id" 
					prop="id"
					show-overflow-tooltip 
					width="100">
            </el-table-column>
			
            <el-table-column
                    prop="creator.nickname,creator.avatar_url,creator.id"
                    label="团长"
                    width="300">
					<template slot-scope="scope">
					    <div flex="box:first">
					        <div style="padding-right: 10px;">
					            <com-image mode="aspectFill" :src="scope.row.creator.avatar_url"></com-image>
					        </div>
							<div flex="cross:top cross:center">
								{{scope.row.creator.nickname}}
							</div>
						</div>
					</template>
					
			</el-table-column>
            <el-table-column
                    prop="start_at,end_at"
                    label="发起时间"
                    width="300">
					<template slot-scope="scope"> <div>{{scope.row.start_at}}至</div><div>{{scope.row.end_at}}</div></template>
            </el-table-column>
            <el-table-column
                    prop="remaining_time,status"
                    label="剩余时间"
                    width="120">
					<template slot-scope="scope">
					    <div flex="box:center">
							{{scope.row.status==2||scope.row.status==3?0:scope.row.remaining_time}}
						</div>
					</template>
            </el-table-column>
            <el-table-column
                    prop="people"
                    label="成团人数"
                    width="120">
            </el-table-column>
            <el-table-column
                    prop="actual_people"
                    label="已拼人数"
                    width="120">
            </el-table-column>
			<el-table-column
                    prop="status"
                    label="拼团状态"
                    width="120">
					<template slot-scope="scope">
					    <div v-if="scope.row.status==0" flex="box:center">
							未拼单
						</div>
						<div v-if="scope.row.status==1" flex="box:center">
							拼单中
						</div>
						<div v-if="scope.row.status==2" flex="box:center">
							拼单成功
						</div>
						<div v-if="scope.row.status==3" flex="box:center">
							拼单失败
						</div>
					</template>
            </el-table-column>
			

            <el-table-column
                    fixed="right"
                    label="操作"
                    width="260">
                <template slot-scope="scope">
                    <el-button @click="groupMemberList(scope.row.id)" type="text" size="small">拼团成员</el-button>
					<!-- 不用看对应团的订单列表，在拼团成员中可以看订单详情 -->
                    <!-- <el-button @click="toOrderList" type="text" size="small">订单</el-button> -->
					<!-- 虚拟成团才可以结束拼团 -->
                    <el-button v-if="scope.row.is_virtual==1&&scope.row.status==1" @click="del(scope.row.id,scope.$index)" type="text" size="small">结束拼团</el-button>
					
                </template>
            </el-table-column>
        </el-table>
		
		                   
		
		<!-- 这里就是el-table的分页数 -->
		<!-- <div class="block1">
		            <el-pagination
		              @size-change="handleSizeChange"
		              @current-change="handleCurrentChange"  //页面变化时的函数
		              :current-page.sync="currentPage1"     // 设置默认页，在data中为1
		              :page-size="7"            // element 是根据page-size 和 total 来显示你有多少页码。如果不在此设置page-size,只在data中设置 pageSize不行的
		              layout="total,prev, pager, next"
		              :total="total">
		            </el-pagination>
		</div> -->
		<div flex="main:right cross:center" style="margin-top: 20px;">
		    <div v-if="pageCount > 0">
		    <!-- <div v-if="pageCount > 0"> -->
		        <el-pagination
		                @current-change="changePagination" 	
		                background
		                :current-page="current_page"
		                layout="prev, pager, next"
		                :page-count="pageCount">
						<!-- page-count总的页面数 -->
		        </el-pagination>
		    </div>
		</div>
		
    </template>
    </div>
	<!-- 拼团成员列表弹窗组件 -->
	<el-dialog title="拼团成员" :visible.sync="dialogFormVisible">
	  <el-table :data="memberList">
	    <el-table-column property="user.avatar_url" label="用户头像" width="150">
			<template slot-scope="scope">
				<el-image :src="scope.row.user.avatar_url"
					 style="width: 50px;height: 50px; margin-right: 10px;">
				</el-image>
			</template>
		</el-table-column>
	    <el-table-column property="user.nickname" label="昵称" width="150"></el-table-column>
	    <el-table-column property="is_creator" label="角色" width="100">
			<template slot-scope="scope">
				<span v-html="scope.row.is_creator=='1'?'团长':'团员'"></span>
			</template>
		</el-table-column>
	    <el-table-column property="group_buy_price" label="订单金额" width="150">
			<template slot-scope="scope">
				<span>￥{{scope.row.group_buy_price}}</span>
			</template>
		</el-table-column>
	    <el-table-column property="created_at_format" label="下单时间" width="300"></el-table-column>
	    <el-table-column property="order_id" label="操作">
			<template slot-scope="scope">
			    <el-button @click="toOrderDetail(scope.row.order_id)" type="text" size="small">订单详情</el-button>
			</template>
		</el-table-column>
	  </el-table>
	</el-dialog>
	
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading:true,
                list: [],
                pagination: {total: 0},
                loading: false,
				
				group_buy_id : '',	//拼团活动id
				pageCount: 0,		//总的页面数
				current_page: 1,	//默认显示第一页
				search : {},		//这个是搜索来的字段
				
				memberList: [{
				  date: '2016-05-02',
				  name: '王小虎',
				  address: '上海市普陀区金沙江路 1518 弄'
				}, {
				  date: '2016-05-04',
				  name: '王小虎',
				  address: '上海市普陀区金沙江路 1518 弄'
				}, {
				  date: '2016-05-01',
				  name: '王小虎',
				  address: '上海市普陀区金沙江路 1518 弄'
				}, {
				  date: '2016-05-03',
				  name: '王小虎',
				  address: '上海市普陀区金沙江路 1518 弄'
				}],
				dialogFormVisible: false,
            }
        },
        methods: {
			// 0.1 点击搜索--这个也是要发请求
			toSearch(e){
				console.log(e);
				let searchData = e;
				let others = {group_buy_id : this.group_buy_id}
				// 创建一个search请求对象--可以用es6方法const {a1,b1} = a; const b ={a1,b1}
				let search={};
				searchData.nickname?search['nickname']=searchData.nickname:'';
				searchData.begin_time?search['begin_time']=searchData.begin_time:'';
				searchData.end_time?search['end_time']=searchData.end_time:'';
				searchData.status=="选择拼团状态"?'':search['status']=searchData.status;
				this.current_page = 1;
				this.search = search;
				// 不是空对象发请求
				if(JSON.stringify(search) != "{}"){
					this.loadData(this.current_page,others,search);
				}
			},
			
			
			// 页面跳转
            toPageAdd() {
                navigateTo({
                    r: 'plugin/group_buy/mall/index/add',
                })
            },
			// 跳转拼团订单列表
			toOrderList() {
                navigateTo({
                    r: 'plugin/group_buy/mall/order/list',
                })
            },
			// 跳转拼团订单列表
			toGroupList(){
				navigateTo({
				    r: 'plugin/group_buy/mall/group/list',
				})
			},
			
			// 跳转到拼团订单详情
			toOrderDetail(order_id){
				navigateTo({
				    r: 'plugin/group_buy/mall/order/detail',
					order_id:order_id,	//直接跳转到订单详情页，但是要确保获取时字段统一
				})
			},
			
			
			//页面数变化，获取新的数据--获取到页面数,发请求就完事了
			changePagination(e) {
				console.log(e);
				this.current_page = e;	//当前页
				let others = {group_buy_id : this.group_buy_id}
				this.loadData(this.current_page,others,this.search);
			},
			
			
			
            // toPageEdit(goods_id) {
            //     navigateTo({
            //         r: 'plugin/group_buy/mall/index/edit',
            //         id:goods_id,
            //     })
            // },
			
			// 结束该团
            del(group_id,index) {
                this.loading = true;
				console.log(group_id+'-'+index);
                request({
                    params: {
                        r: 'plugin/group_buy/mall/group/manual-end',
                        id: group_id
                    },
                    method: 'get',
                }).then(e => {
                    let res = e.data;
                    this.listLoading = false;
                    if (res.code === 0) {
                        this.list.splice(index, 1);
                        this.$message.success(res.msg);
                    } else {
                        this.$message.error(res.msg);
                    }
                }).catch(e => {
                });
            },
			
			// 请求获取数据--获取活动的开团列表
            loadData(page,others,search) {
                this.loading = true;
                request({
                    params: { 
                        r: 'plugin/group_buy/mall/group/get-list',
                        page: page,
						// limit : 5,
						...others,
						...search
                    },
                    method: 'POST',
                }).then(e => {
                    let res = e.data;
                    this.listLoading = false;
                    if (res.code === 0) {
                        this.list = res.data.list;
                        this.pageCount = res.data.pagination.page_count;	//返回了总的页面数
						
						console.log(this.list);
                    } else {
                        this.$message.error(res.msg);
                    }
                }).catch(e => {
                });
            },
			
			// 获取该团下的拼团成员列表
			groupMemberList(id) {
			    this.loading = true;
			    request({
			        params: {
			            r: 'plugin/group_buy/mall/active/get-list',
						active_id : id
			        },
			        method: 'POST',
			    }).then(e => {
			        let res = e.data;
			        this.listLoading = false;
			        if (res.code === 0) {
						// 请求成功才显示弹窗列表
						this.dialogFormVisible = true;
						console.log(res.data.list);
			            this.memberList = res.data.list;
			        } else {
			            this.$message.error(res.msg);
			        }
			    }).catch(e => {
			    });
			},

            formatStatus: function (row) {
                switch (row.status) {
                    case '0':
                        return '未开始';

                    case '1':
                        return '开团中';

                    case '2':
                        return '已结束';
                    default:
                        return '未知';
                }
            },
        },
		// 加载的时候获取到列表页
		created(){
			// 获取到活动id
			if(getQuery('id')){
				console.log(getQuery('id'));
				this.group_buy_id = getQuery('id');
			}
		},
        mounted(){
			// 数据初始化
			this.current_page = 1;
			this.pageCount = 0;
			// 默认是拿到对应拼团id下的列表数据
			let others = {group_buy_id : this.group_buy_id}
            this.loadData(this.current_page,others,this.search);
        },
    })
</script>

