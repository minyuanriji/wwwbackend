<!--  购物券区-来源设置-添加商户-选择商户 （专用）   -->
<style>
    .com-dialog-dialog {
        min-width: 700px;
    }
    #hunt {
        width: 30%
    }
</style>
<template id="com-shop-store-list">
    <div class="com-shop-store-list">
        <el-dialog append-to-body :title="title" :visible.sync="visible" :close-on-click-modal="false"
                   custom-class="com-dialog-dialog" @close="closeDialog">
            <div>
                <div style="display: flex;justify-content: space-evenly">
                    <div id="hunt">
                        <el-form @submit.native.prevent size="small" >
                            <el-form-item label="等级">
                                <el-select v-model="level" placeholder="请选择区域等级" @change="levelChange">
                                    <el-option
                                            v-for="item in level_list"
                                            :label="item.name"
                                            :value="item.level">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div id="hunt">
                        <template v-if="level>0">
                            <el-form @submit.native.prevent size="small">
                                <el-form-item label="省市区" prop="address">
                                    <el-cascader
                                            @change="addressChange"
                                            :options="district"
                                            :props="props"
                                            v-model="address">
                                    </el-cascader>
                                </el-form-item>
                            </el-form>
                        </template>
                    </div>
                    <div id="hunt">
                        <el-input v-model="search.keyword" placeholder="根据名称搜索" @keyup.enter.native="getDetail(1)">
                            <el-button slot="append" @click="getDetail(1)">搜索</el-button>
                        </el-input>
                    </div>
                </div>

                <el-table border v-loading="listLoading" :data="list" style="margin-top: 24px;"
                          @selection-change="handleSelectionChange">

                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column prop="id" width="80" label="ID"></el-table-column>


                    <el-table-column label="名称">
                        <template slot-scope="props">
                            <com-ellipsis :line="2">{{props.row[listKey]}}</com-ellipsis>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div style="margin-top: 24px;">
                <el-row>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="getDetail"
                            layout="prev, pager, next"
                            :total="pagination.total_count">
                    </el-pagination>
                    <el-button type="primary" size="small" style="float: right" @click="confirm">选择</el-button>
                </el-row>
            </div>
        </el-dialog>
        <div @click="click" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('com-shop-store-list', {
        template: '#com-shop-store-list',
        props: {
            url: {
                type: String,
                default: 'plugin/mch/mall/mch/mch-list'
            },
            multiple: Boolean,
            title: {
                type: String,
                default: '门店选择'
            },
            listKey: {
                type: String,
                default: 'name'
            },
            params: Object,
            value: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                visible: false,
                listLoading: false,
                list: [],
                pagination: null,
                radioSelection: 0,
                search: {
                    keyword: ''
                },
                multipleSelection: [],
                level_list: [
                    {
                        name: '省',
                        level: 4
                    },
                    {
                        name: '市',
                        level: 3
                    },
                    {
                        name: '区',
                        level: 2
                    },
                ],
                level: '',
                district: [],
                address: null,
                town_list: [],
                town_id: '',
                province_id: 0,
                city_id: 0,
                district_id: 0,
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
            }
        },

        methods: {
            addressChange(e) {
                this.town_list = []
                this.town_id = '';

                if (e.length == 3) {
                    this.getTownList(e[2]);
                }
            },
            getTownList(district_id) {
                request({
                    params: {
                        r: 'district/town-list',
                        district_id: district_id
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.town_list = e.data.list;
                    }
                }).catch(e => {
                });

            },
            levelChange(e) {
                this.getDistrict(e);
            },
            // 获取省市区列表
            getDistrict(level) {
                if (level == 4) {
                    level1 = 1;
                } else if (level == 3) {
                    level1 = 2;
                } else if (level == 2) {
                    level1 = 3;
                } else {
                    level1 = 4;
                }
                request({
                    params: {
                        r: 'district/index',
                        level: level1
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            closeDialog(){
                this.visible=false;
            },
            click() {
                this.getDetail(1);
                this.visible = !this.visible;
            },
            getDetail(page) {
                this.list = [];
                this.listLoading = true;
                let params = Object.assign({
                    r: this.url,
                    keyword: this.search.keyword,
                    address: this.address,
                    page: page
                }, this.params);
                request({
                    params: params
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            handleSelectionChange(val) {
                console.log(val);
                this.multipleSelection = val;
            },
            confirm() {
                this.$emit('selected', this.multipleSelection);
                this.visible = false;
                this.$emit('input', this.multipleSelection);
            }
        }
    });
</script>