<div id="app">
    <el-card class="box-card">
        <div slot="header" class="clearfix" style="height: 20px;">
            <span>多人拼团</span>
            <el-button style="float: right; padding: 3px 12px;margin-left: 20px;height: 32px;" type="primary" @click="toPageAdd">添加拼团商品</el-button>
            <!-- <el-button style="float: right; padding: 3px 0" type="text" @click="toOrderList">拼团订单列表</el-button> -->
        </div>
		<div slot="header" class="clearfix" style="margin-top: 20px;">
            <el-button :type="LoadDataType==3?'primary':''" size="small" @click="getLoadData(3)">全部</el-button>
            <el-button :type="LoadDataType==0?'primary':''" size="small" @click="getLoadData(0)">未开始</el-button>
            <el-button :type="LoadDataType==1?'primary':''" size="small" @click="getLoadData(1)">活动中</el-button>

			
			<div class="demo-input-suffix" flex="dir:right cross:center">
				<el-button type="primary" size="small" @click="toSearchActivity">搜索</el-button>
				<el-input 
					style="width:300px;margin-right: 20px;"
					placeholder="搜索商品名称"
					v-model="searchActivity"
					clearable>
				</el-input>
			</div>
			
        </div>
		
    </el-card>

    <div class="table-body">
    <template>
        <el-table
                :data="list"
                border
                style="width: 100%">
            <el-table-column
                    fixed
					type="index" 
                    label="编号"
					show-overflow-tooltip 
                    width="100">
            </el-table-column>
            <el-table-column
                    prop="goods.name,goods.cover_pic"
                    label="商品名称"
                    width="300">
					<template slot-scope="scope">
					    <div flex="box:first">
					        <div style="padding-right: 10px;">
					            <com-image mode="aspectFill" :src="scope.row.goods.cover_pic"></com-image>
					        </div>
							<div flex="cross:top cross:center">
								{{scope.row.goods.name}}
							</div>
						</div>
					</template>
            </el-table-column>
            <el-table-column
                    prop="status"
                    label="状态"
                    :formatter="formatStatus"
                    width="120">
            </el-table-column>
            <el-table-column
                    prop="start_at"
                    label="开始时间"
                    width="300">
            </el-table-column>
            <el-table-column
                    prop="total_goods_buy_order"
                    label="成团订单数"
                    width="120">
            </el-table-column>
            <el-table-column
                    prop="total_goods_buy_order_amount"
                    label="成团金额"
                    width="120">
            </el-table-column>
            <el-table-column
                    prop="goods_stock"
                    label="拼团商品库存"
                    width="120">
            </el-table-column>
			<el-table-column
                    prop="is_virtual"
                    label="是否虚拟成团"
                    width="120">
					<template slot-scope="scope">
					    <div flex="box:center">
							{{scope.row.is_virtual==1?'是':'否'}}
						</div>
					</template>
            </el-table-column>
			

            <el-table-column
                    fixed="right"
                    label="操作"
                    width="200">
                <template slot-scope="scope">
                    <el-button @click="groupGoodsDetail(scope.row.goods_id)" type="text" size="small">查看</el-button>
                    <el-button @click="del(scope.row.goods_id,scope.$index)" type="text" size="small">删除</el-button>
                    <el-button @click="toGroupList(scope.row.id)" type="text" size="small">开团列表</el-button>
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
	
	<!-- 这个是查看商品详情弹窗 -->
	<el-dialog title="查看明细" :visible.sync="dialogFormVisible">
	  <el-table :data="goodsDetail.attr" :span-method="objectSpanMethod">
		<el-table-column property="user.avatar_url" label="商品名称" width="150" header-align="center" align="center">
			<template slot-scope="scope">
				<span>{{goodsDetail.name}}</span>
			</template>
		</el-table-column>
	    <el-table-column property="attr_list" label="商品SKU" width="200">
			<template slot-scope="scope">
				<span v-for="(item,index) in scope.row.attr_list" :key="index">{{item.attr_name}} </span>
			</template>
		</el-table-column>
	    <el-table-column property="price" label="单价" width="150"></el-table-column>
	    <el-table-column property="group_buy_price" label="拼团价" width="150"></el-table-column>
	    <el-table-column property="stock" label="当前库存"></el-table-column>
		
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
				LoadDataType : 3,	//默认显示全部数据
				
				pageCount: 0,		//总的页面数
				current_page: 1,	//默认显示第一页
				
				dialogFormVisible : false,	//默认不显示弹窗
				goodsDetail : {},
				
				searchActivity : '',	//搜索活动名称
            }
        },
        methods: {
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
			
			// 跳转到当前商品活动的开团列表
			toGroupList(id){
				console.log(id);
				navigateTo({
				    r: 'plugin/group_buy/mall/group/list',
					id : id
				})
			},
			
			//状态按钮的点击事件
			getLoadData(e){
				let self = this;
				// 默认显示第一页数据
				self.current_page = 1;
				if(e==self.LoadDataType){	//如果没有变化，就不要做请求和赋值了
					return false;
				}
				self.LoadDataType = e;
				let others = {status : self.LoadDataType}
				// 前端自定义3为全部，全部的时候不发状态码
				self.LoadDataType==3 ? self.loadData(self.current_page) : self.loadData(self.current_page,others);
				
			},
			
			// 3.0 点击搜索商品名称
			toSearchActivity(){
				console.log(this.searchActivity);
				// 发送请求获取对应的商品
				let others = {};
				others['goods_name']=this.searchActivity;
				this.LoadDataType==3?'':others['status']=this.LoadDataType;
				!this.searchActivity?'':this.loadData(this.current_page,others);
			},
			
			//页面数变化，获取新的数据--获取到页面数,发请求就完事了
			changePagination(e) {
				console.log(e);
				this.current_page = e;
				let others = {status : this.LoadDataType}
				// 前端自定义3为全部，全部的时候不发状态码
				this.LoadDataType==3 ? this.loadData(this.current_page) : this.loadData(this.current_page,others);
				
			},
			
			// 合并列的方法
			objectSpanMethod({ row, column, rowIndex, columnIndex }) {
				if (columnIndex === 0) {	//选中第一列
				  if (rowIndex === 0) {		//第一行合并所有行
					return {
					  rowspan: this.goodsDetail.attr.length,
					  colspan: 1
					};
				  }else{					//其他全省略
					   return {
						rowspan: 0,
						colspan: 0
					  };
				  }
				}
		    },
			
			// 查看商品详情
			groupGoodsDetail(goods_id){
				console.log(goods_id);
				this.loading = true;
				request({
				    params: {
				        r: 'plugin/group_buy/mall/index/show',
				        id: goods_id,
				    },
				    method: 'GET',
				}).then(e => {
				    let res = e.data;
				    this.listLoading = false;
				    if (res.code === 0) {
						this.dialogFormVisible = true;
						let goodsDetail = res.data.detail;
						let group_buy_goods = res.data.group_buy_goods;
						if(goodsDetail.attr.length>0&&group_buy_goods.group_buy_goods_attr.length>0){
							goodsDetail.attr.forEach(function (item, index) {
								group_buy_goods.group_buy_goods_attr.forEach(function (its, idx) {
									if(item.id==its.attr_id){
										item.stock = its.stock;
									}
								});
							});
						}
				        this.goodsDetail = goodsDetail;
						console.log(this.goodsDetail);
						
				    } else {
				        this.$message.error(res.msg);
				    }
				}).catch(e => {
				});
			},
			
			
			// 跳转编辑页
            toPageEdit(goods_id) {
                navigateTo({
                    r: 'plugin/group_buy/mall/index/edit',
                    id:goods_id,
                })
            },
			
			// 删除拼团商品
			del(goods_id,index) {
				this.$confirm('此操作将删除该拼团商品, 是否继续?', '提示', {
				  confirmButtonText: '确定',
				  cancelButtonText: '取消',
				  type: 'warning'
				}).then(() => {
					this.delGroupGoods(goods_id,index);
				}).catch(() => {
				  this.$message({
					type: 'info',
					message: '已取消删除'
				  });          
				});
		    },
			
            delGroupGoods(goods_id,index) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/group_buy/mall/index/del',
                        goods_id: goods_id
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
			
			// 请求获取数据--获取拼团商品列表
            loadData(page,others) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/group_buy/mall/index/get-list',
                        page: page,
						limit : 5,
						...others
                    },
                    method: 'get',
                }).then(e => {
                    let res = e.data;
                    this.listLoading = false;
                    if (res.code === 0) {
                        this.list = res.data.list;
                        this.pagination = res.data.pagination;		//拿到关于页码页容量的数据
						this.pageCount = res.data.pagination.page_count;
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
        mounted(){
			// 数据初始化
			this.current_page = 1;
			this.pageCount = 0;
			this.LoadDataType = 3;
			// 不传others,默认是拿到所有的列表数据
            this.loadData(this.current_page);
        }
    })
</script>

