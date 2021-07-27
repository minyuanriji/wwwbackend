<style>
    .com-search .tabs {
        margin-top: 20px;
    }

    .com-search .label {
        margin-right: 10px;
    }

    .com-search .item-box {
        margin-bottom: 10px;
        margin-right: 15px;
    }

    .com-search .clear-where {
        color: #419EFB;
        cursor: pointer;
    }
</style>

<template id="com-search">
    <div class="com-search">
        <div flex="wrap:wrap cross:center">
			
			<div class="item-box" flex="dir:left cross:center">
			    <div class="label">团长昵称</div>
				<el-input @input="checkSearch()" style="width: 160px;" v-model="search.nickname" placeholder="请输入内容"></el-input>
			</div>
			
            <div style="height: 32px;">时间范围：</div>
            <el-date-picker
                    class="item-box"
                    size="small"
                    @change="changeTime"
                    v-model="search.time"
                    type="datetimerange"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    range-separator="至"
                    start-placeholder="开始时间"
                    end-placeholder="结束时间">
            </el-date-picker>
           
            <div v-if="isShowOrderPlugin" class="item-box" flex="dir:left cross:center">
                <div class="label">拼团状态</div>
                <el-select size="small" style="width: 160px" v-model="search.status" @change="toSearch"
                           placeholder="拼团状态">
                    <el-option v-for="item in plugins" :key="item.sign" :label="item.name"
                               :value="item.sign">
                    </el-option>
                </el-select>
            </div>
			
            <div class="item-box" flex="cross:center">
                <div v-if="isShowClear" @click="clearWhere" class="div-box clear-where">清空筛选条件</div>
            </div>
			
			<!-- 搜索按钮 -->
			<el-button
			        style="float: right;margin: -10px 0 0 50px;"
			        type="primary"
			        size="small"
			        @click="toSearch">搜索
			</el-button>
			
        </div>
		
    </div>
</template>

<script>
    Vue.component('com-search', {
        template: '#com-search',
        props: {
            selectList: {
                type: Array,
                default: function () {
                    return [
                        {value: '1', name: '订单号'},
                        {value: '9', name: '商户单号'},
                        {value: '2', name: '用户名'},
                        {value: '4', name: '用户ID'},
                        {value: '5', name: '商品名称'},
                        {value: '3', name: '收件人'},
                        {value: '6', name: '收件人电话'},
                        {value: '7', name: '门店名称'}
                    ]
                }
            },
           
            plugins: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '未拼单',
                            sign: '0',
                        },
						{
                            name: '拼单中',
                            sign: '1',
                        },
						{
                            name: '拼单成功',
                            sign: '2',
                        },
						{
                            name: '拼单失败',
                            sign: '3',
                        },
						
                    ];
                }
            },
            isShowOrderType: {
                type: Boolean,
                default: true
            },
            isShowOrderPlugin: {
                type: Boolean,
                default: false
            },
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        time: null,
						nickname:'',
                        keyword_1: '1',
                        begin_time: '',
                        end_time: '',
                        status: '选择拼团状态',
                    }
                }
            },
            dateLabel: {
                type: String,
                default: '下单时间'
            }
        },
        data() {
            return {
                search: {},
                isShowClear: false,
            }
        },
        methods: {
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.begin_time = this.search.time[0];
                    this.search.end_time = this.search.time[1];
                } else {
                    this.search.begin_time = null;
                    this.search.end_time = null;
                }
                this.toSearch();
            },
            toSearch() {
                this.search.page = 1;
                this.$emit('search', this.search);
                this.checkSearch();
            },
           
			//初始化搜索字段
            clearWhere() {
                this.search.nickname = '';
                this.search.begin_time = null;
                this.search.end_time = null;
                this.search.time = null;
                this.search.status = '选择拼团状态';
                this.toSearch();
            },
			// 是否显示清除按钮
            checkSearch() {
                if (this.search.nickname || (this.search.begin_time && this.search.end_time)
                    || this.search.status != '选择拼团状态' ) {
                    this.isShowClear = true;
                } else {
                    this.isShowClear = false;
                }
            }
        },
        created() {
			// 创建一个search对象
            this.search = this.newSearch;
            this.checkSearch();
        }
    })
</script>