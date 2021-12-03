<style>
    .new-table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分类列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加分类</el-button>
                </div>
            </div>
        </div>
        <div class="new-table-body">
            <div class="com-cat-list">
                <el-form size="small" :inline="true" :model="search" @submit.native.prevent>
                    <template v-if="!isEditSort">
                        <el-form-item>
                            <div class="input-item">
                                <el-input @keyup.enter.native="searchCat" clearable @clear="searchCat" size="small"
                                          placeholder="请输入搜索内容"
                                          v-model="search.keyword">
                                    <el-button slot="append" icon="el-icon-search" @click="searchCat"></el-button>
                                </el-input>
                            </div>
                        </el-form-item>
                    </template>
                    <el-form-item v-if="!isEditSort">
                        <el-button @click="isEditSort=true" style="margin-left: 10px" type="primary">编辑排序</el-button>
                    </el-form-item>
                    <el-form-item v-if="isEditSort">
                        <el-button :loading="submitLoading" @click="storeSort" style="margin-left: 10px" type="primary">保存排序
                        </el-button>
                        <el-button @click="isEditSort=false" style="margin-left: 10px">取消编辑
                        </el-button>
                        <span style="margin-left: 10px;">拖动分类名称排序</span>
                    </el-form-item>
                </el-form>
                <div class="cat-list" flex="dir:left box:mean">
                    <el-card v-loading="listLoading" shadow="never" class="card-item-box"
                             body-style="padding:0;height: 500px;overflow:auto">
                        <div slot="header">
                            一级分类
                        </div>
                        <div v-if="first_cat_list.length > 0" style="overflow:auto" @scroll="firstScroll">
                            <draggable v-model="first_cat_list" :options="{disabled:!isEditSort}">
                                <div :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                                     @click="select(item)"
                                     v-for="(item,index) in first_cat_list"
                                     class="cat-item"
                                     :class="first_cat.id == item.id ? 'active':''">
                                    <el-row flex="cross:center" style="height: 50px">
                                        <el-col :span="4">
                                            <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                                <div class="cat-id">{{item.id}}</div>
                                            </el-tooltip>
                                        </el-col>
                                        <el-col :span="13" flex="cross:center">
                                            <com-image class="cat-icon" :src="item.pic_url" width="30px"
                                                       height="30px"></com-image>
                                            <div class="cat-name-info">
                                                <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                                    <span>{{item.name}}</span>
                                                </el-tooltip>
                                            </div>
                                        </el-col>
                                        <el-col :span="7">
                                            <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                                     @submit.native.prevent>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="edit(item.id)">
                                                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                            <img src="statics/img/mall/edit.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="destroy(item)">
                                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                            <img src="statics/img/mall/del.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                            </el-form>
                                        </el-col>
                                    </el-row>
                                </div>
                            </draggable>
                        </div>

                    </el-card>
                    <el-card v-loading="listLoading_2" shadow="never" class="card-item-box"
                             body-style="padding:0;height: 500px;overflow:auto">
                        <div v-if="first_cat.child.length > 0" slot="header">
                            二级分类
                        </div>
                        <div v-if="first_cat.child.length > 0" style="overflow:auto" @scroll="scrollAgain">
                            <draggable v-model="first_cat.child" :options="{disabled:!isEditSort}">
                                <div @click="selectAgain(item)"
                                     :class="sec_cat.id == item.id ? 'active':''"
                                     v-for="(item,index) in first_cat.child"
                                     :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                                     class="cat-item">
                                    <el-row flex="cross:center" style="height: 50px;">
                                        <el-col :span="4">
                                            <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                                <div class="cat-id">{{item.id}}</div>
                                            </el-tooltip>
                                        </el-col>
                                        <el-col :span="13" flex="cross:center">
                                            <com-image class="cat-icon" :src="item.pic_url" width="30px"
                                                       height="30px"></com-image>
                                            <div class="cat-name-info">
                                                <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                                    <span>{{item.name}}</span>
                                                </el-tooltip>
                                            </div>
                                        </el-col>
                                        <el-col :span="7">
                                            <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                                     @submit.native.prevent>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="edit(item.id)">
                                                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                            <img src="statics/img/mall/edit.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="destroy(item)">
                                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                            <img src="statics/img/mall/del.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                            </el-form>
                                        </el-col>
                                    </el-row>
                                </div>
                            </draggable>
                        </div>
                    </el-card>
                    <el-card v-loading="listLoading_3" shadow="never" class="card-item-box"
                             body-style="padding:0;height: 500px;overflow:auto">
                        <div v-if="sec_cat.child.length > 0" slot="header">三级分类</div>
                        <div v-if="sec_cat.child.length > 0">
                            <draggable v-model="sec_cat.child" :options="{disabled:!isEditSort}">
                                <div @click="selectThird(item)"
                                     v-for="(item,index) in sec_cat.child"
                                     class="cat-item"
                                     :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                                     :class="third_cat_id == item.id ? 'active':''">
                                    <el-row flex="cross:center" style="height:50px;">
                                        <el-col :span="4">
                                            <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                                <div class="cat-id">{{item.id}}</div>
                                            </el-tooltip>
                                        </el-col>
                                        <el-col :span="13" flex="cross:center">
                                            <com-image class="cat-icon" :src="item.pic_url" width="30px"
                                                       height="30px"></com-image>
                                            <div class="cat-name-info">
                                                <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                                    <span>{{item.name}}</span>
                                                </el-tooltip>
                                            </div>
                                        </el-col>
                                        <el-col :span="7">
                                            <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                                     @submit.native.prevent>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="edit(item.id)">
                                                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                            <img src="statics/img/mall/edit.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                                <el-form-item>
                                                    <el-button style="display: block;" type="text"
                                                               class="set-el-button"
                                                               size="mini" circle @click="destroy(item)">
                                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                            <img src="statics/img/mall/del.png" alt="">
                                                        </el-tooltip>
                                                    </el-button>
                                                </el-form-item>
                                            </el-form>
                                        </el-col>
                                    </el-row>
                                </div>
                            </draggable>
                        </div>
                    </el-card>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                first_cat: {
                    child: []
                },
                sec_cat: {
                    child: []
                },
                third_cat_id: null,
                listLoading: false,
                listLoading_2: false,
                listLoading_3: false,
                page: 1,
                pageCount: 0,
                first_cat_list: [],
                search: {
                    keyword: ''
                },
                searchList: [],
                searchFinish: false,
                editSortVisible: false,
                editSortForm: {
                    sort: 100,
                },
                submitLoading: false,
                isEditSort: false,
            };
        },
        methods: {

            // 搜索
            searchCat() {
                let self = this;
                self.searchList = [];
                if (self.search.keyword == '') {
                    this.getList();
                    return false
                }
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/search',
                        page: 1,
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.searchFinish = true;
                    self.searchList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取数据
            getList() {
                let self = this;
                self.list = [];
                self.sec_cat.child = [];
                self.first_cat.child = [];
                self.listLoading = true;
                self.listLoading_2 = true;
                self.listLoading_3 = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/get-list',
                        page: self.page,
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.listLoading_2 = false;
                    self.listLoading_3 = false;
                    self.list = e.data.data.list;
                    self.first_cat_list = e.data.data.list;
                    if (e.data.data.list.length > 0) {
                        self.first_cat = self.first_cat_list[0]
                        if (self.first_cat.child.length > 0) {
                            self.sec_cat = self.first_cat.child[0]
                        }
                    }
                }).catch(e => {
                    self.listLoading = false;
                    self.listLoading_2 = false;
                    self.listLoading_3 = false;
                    console.log(e);
                });
            },
            // 一级分类滚动加载更多
            firstScroll(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.list.length == 20) {
                    this.page += 1;
                    this.getList();
                }
            },
            // 二级分类滚动加载更多
            scrollAgain(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.first_cat.child.length == 20) {
                    this.page_2 += 1;
                    this.children_2();
                }
            },
            // 三级分类滚动加载更多
            thirdScroll(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.sec_cat.child.length == 20) {
                    this.page_3 += 1;
                    this.children_3();
                }
            },
            // 编辑
            edit(id) {
                navigateTo({
                    r: 'plugin/taolijin/mall/cat/edit',
                    id: id,
                });
            },
            // 删除
            destroy(row) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/taolijin/mall/cat/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
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
            // 选中一级分类
            select(row) {
                if (this.isEditSort) {
                    return;
                }
                this.first_cat = row;
                this.sec_cat = {
                    child: []
                }
            },
            // 选中二级分类
            selectAgain(row) {
                if (this.isEditSort) {
                    return;
                }
                this.sec_cat = row;
            },
            selectThird(row) {
                if (this.isEditSort) {
                    return;
                }
                this.third_cat_id = row.id;
            },
            // 二级分类列表
            children_2() {
                let self = this;
                self.listLoading_2 = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/children-list',
                        id: self.first_cat.id,
                        page: self.page_2
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading_2 = false;
                    if (self.page_2 == 1) {
                        self.first_cat.child = e.data.data.list;
                    } else {
                        self.first_cat.child.concat(e.data.data.list);
                    }
                    self.pageCount_2 = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 三级分类列表
            children_3() {
                let self = this;
                self.listLoading_3 = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/children-list',
                        id: self.sec_cat.id,
                        page: self.page_3
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading_3 = false;
                    if (self.page_3 == 1) {
                        self.sec_cat.child = e.data.data.list;
                    } else {
                        self.sec_cat.child.concat(e.data.data.list);
                    }
                    self.pageCount_3 = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            storeSort() {
                let self = this;
                self.submitLoading = true;

                let firstList = [];
                let secondList = [];
                let thirdList = [];

                self.first_cat_list.forEach(function (item) {
                    firstList.push({
                        id: item.id,
                        name: item.name
                    })
                });
                self.first_cat.child.forEach(function (item) {
                    secondList.push({
                        id: item.id,
                        name: item.name
                    })
                })
                self.sec_cat.child.forEach(function (item) {
                    thirdList.push({
                        id: item.id,
                        name: item.name
                    })
                })

                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/sort'
                    },
                    method: 'post',
                    data: {
                        first_list: JSON.stringify(firstList),
                        second_list: JSON.stringify(secondList),
                        third_list: JSON.stringify(thirdList),
                    }
                }).then(e => {
                    self.submitLoading = false;
                    if (e.data.code === 0) {
                        self.isEditSort = false;
                        self.$message.success(e.data.msg);
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.submitLoading = false;
                });
            }
        },
        mounted: function () {
            this.getList();
        },
    });
</script>

<style>
    .com-cat-list .input-item {
        display: inline-block;
        width: 250px;
    }

    .com-cat-list .input-item .el-input__inner {
        border-right: 0;
    }

    .com-cat-list .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .com-cat-list .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .com-cat-list .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .com-cat-list .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .com-cat-list .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .com-cat-list .table-body .cat-item {
        display: flex;
        justify-content: space-between;
        height: 65px;
        align-items: center;
        padding-left: 5px;
        border-top: 1px solid #F5F5F5;
        color: #000000;
        cursor: pointer;
        width: 100%;
        /*min-width: 350px*/
    }

    .com-cat-list .active {
        background-color: #F5F5F5;
        /*color: #3399FF;*/
    }

    .com-cat-list .table-body .cat-item:first-of-type {
        border-top: 0;
    }

    .com-cat-list .table-body .cat-item .cat-name {
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .com-cat-list .table-body .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .com-cat-list .table-body .cat-item .el-form-item .el-button {
        padding: 0;
        margin: 0 5px;
    }

    .com-cat-list .table-body .cat-item .el-input {
        width: 100px;
    }

    /*.com-cat-list .cat-item:hover .edit-sort {*/
    /*display: inline-block;*/
    /*}*/

    .com-cat-list .change {
        width: 80px;
    }

    .com-cat-list .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
        padding: 0;
    }

    /*.com-cat-list .edit-sort {*/
    /*display: none;*/
    /*}*/

    .com-cat-list .cat-name-info {
        width: 100px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .com-cat-list .cat-list {
        white-space: nowrap;
    }

    .com-cat-list .cat-list .el-card {
        /*margin-left: -5px;*/
        /*width: 546px;*/
        display: inline-block;
        /*vertical-align: top;*/
    }

    .com-cat-list .cat-list .el-card:first-of-type {
        margin-left: 0
    }

    .com-cat-list .cat-list .card-item-box {
        margin-right: 5px;
        height: 552px;
    }

    .com-cat-list .cat-id {
        width: 55px;
        color: #999;
        font-size: 14px;
        margin-left: 5px;
    }

    .com-cat-list .el-form--inline .el-form-item {
        margin-right: 0px;
    }

    .com-cat-list .cat-icon {
        margin-right: 10px;
    }

    .com-cat-list .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .com-cat-list .edit-sort-box .el-button.is-circle {
        padding: 3px;
    }
</style>
