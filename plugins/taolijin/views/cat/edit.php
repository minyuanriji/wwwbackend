<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading" v-loading="cardLoading">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'plugin/taolijin/mall/cat/index'})" class="text">分类</span>/分类编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="选择分类级别">
                    <div flex="dir:left" class="radio-box">
                        <div @click="changeParent(1)">
                            <el-radio :disabled="ruleForm.id ? true : false" v-model="currentRadioKey" :label="1">一级分类</el-radio>
                        </div>
                        <div @click="changeParent(2)">
                            <el-radio :disabled="ruleForm.id ? true : false"  v-model="currentRadioKey" :label="2">二级分类</el-radio>
                        </div>
                        <div @click="changeParent(3)">
                            <el-radio :disabled="ruleForm.id ? true : false" v-model="currentRadioKey" :label="3">三级分类</el-radio>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item v-if="currentCatLevelShow == 2 && currentCatShow.value" label="一级分类">
                    <div flex="dir:left">
                        <div>{{currentCatShow.label}}</div>
                        <div v-if="!ruleForm.id" @click="changeParent(currentCatLevelShow)" class="cat-edit-text">修改</div>
                    </div>
                </el-form-item>
                <template v-if="currentCatLevelShow == 3 && currentCatShow.value">
                    <el-form-item label="一级分类">
                        <div flex="dir:left">
                            <div>{{currentCatShow.label}}</div>
                            <div v-if="!currentCatShow.oneChildren && !ruleForm.id" @click="changeParent(currentCatLevelShow)"
                                 class="cat-edit-text">修改
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item v-if="currentCatShow.oneChildren" label="二级分类">
                        <div flex="dir:left">
                            <div>{{currentCatShow.oneChildren.label}}</div>
                            <div v-if="!ruleForm.id" @click="changeParent(currentCatLevelShow)" class="cat-edit-text">修改</div>
                        </div>
                    </el-form-item>
                </template>
                <el-form-item label="分类名称" prop="name">
                    <el-input v-model="ruleForm.name"></el-input>
                </el-form-item>
                <el-form-item prop="sort">
                    <template slot='label'>
                        <span>排序</span>
                        <el-tooltip effect="dark" content="排序值越大排序越靠前"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input type="number" v-model="ruleForm.sort"></el-input>
                </el-form-item>
                <el-form-item label="分类图标" prop="pic_url">
                    <com-attachment :multiple="false" :max="1" v-model="ruleForm.pic_url">
                        <el-tooltip effect="dark" content="建议尺寸200*200" placement="top">
                            <el-button style="margin-bottom: 10px;" size="mini">选择文件</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <com-gallery :url="ruleForm.pic_url" :show-delete="true" @deleted="ruleForm.pic_url = ''"
                                 width="80px" height="80px">
                    </com-gallery>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
    <div class="el-dialog-1">
        <el-dialog title="选择归属的一级分类" :visible.sync="dialogVisible1" width="20%">
            <el-form v-loading="dialogLoading" size="small" :inline="true" :model="search" @submit.native.prevent>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchCat" size="small" placeholder="请输入分类名称搜索"
                                  v-model="search.keyword" clearable @clear="searchCat">
                            <el-button slot="append" icon="el-icon-search" @click="searchCat"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <div class="dialog-cat-box">
                    <el-table :show-header="false" :data="catList1" highlight-current-row @current-change="tableRowClick" :row-class-name="tableRowClassName" style="width: 100%">
                        <el-table-column prop="label"></el-table-column>
                    </el-table>
                </div>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogVisible1 = false">取 消</el-button>
                <el-button size="small" type="primary" @click="selectCatSubmit">确 定</el-button>
            </span>
        </el-dialog>
    </div>
    <div class="el-dialog-2">
        <el-dialog title="选择归属的二级分类" :visible.sync="dialogVisible2" width="30%">
            <el-form v-loading="dialogLoading" size="small" :inline="true" :model="search" @submit.native.prevent>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchCat" size="small" placeholder="请输入分类名称搜索" v-model="search.keyword" clearable @clear="searchCat">
                            <el-button slot="append" icon="el-icon-search" @click="searchCat"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <div flex="dir:left">
                    <div class="dialog-cat-box">
                        <el-table :show-header="false" :data="catList2" highlight-current-row @current-change="tableRowClick" :row-class-name="tableRowClassName2" style="width: 100%">
                            <el-table-column>
                                <template slot-scope="scope">
                                    <div flex="box:last">
                                        <div>{{scope.row.label}}</div>
                                        <div v-if="scope.row.child">
                                            <i class="el-icon-arrow-right"></i>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="dialog-cat-box">
                        <el-table :show-header="false" :data="catList3" highlight-current-row @current-change="tableRowClick2" :row-class-name="tableRowClassName3" style="width: 100%">
                            <el-table-column prop="label"></el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogVisible2 = false">取 消</el-button>
                <el-button size="small" type="primary" @click="selectCatSubmit">确 定</el-button>
            </span>
        </el-dialog>
    </div>
    <el-dialog :title="imageShowData.title" :visible.sync="imageDialogVisible" width="20%">
        <div class="image-show-box" flex="main:center">
            <image :src="imageShowData.image_url"></image>
        </div>
        <div slot="footer">
            <el-button type="primary" size="small" @click="imageDialogVisible = false">我知道了</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    pic_url: '',
                    parent_id: 0,
                    status: 1,
                    sort: 100
                },
                parentIds: [],
                options: [],
                rules: {
                    name: [
                        {required: true, message: '请输入分类名称', trigger: 'change'},
                        {max: 16, message: '最多输入16个字符', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '请输入排序', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                dialogVisible1: false,
                dialogVisible2: false,
                search: {
                    keyword: ''
                },
                catList1: [],
                catList2: [],
                catList3: [],
                dialogLoading: false,
                currentCat: null,
                currentCatShow: null,
                currentCatLevel: 1,
                currentCatLevelShow: 1,
                imageDialogVisible: false,
                imageShowData: {
                    title: '',
                    image_url: ''
                },
                currentRadioKey: 1,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        this.ruleForm.parent_id = this.parentIds[this.parentIds.length - 1];
                        request({
                            params: {
                                r: 'plugin/taolijin/mall/cat/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/taolijin/mall/cat/index'
                                })
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
                });
            },
            getOptions() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/all-list',
                        id: getQuery('id'),
                        keyword: this.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code === 0) {
                        self.options = e.data.data.list;
                        self.setCatList1();
                        self.setCatList2();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 所有一级分类列表
            setCatList1() {
                let self = this;
                self.catList1 = [];
                self.options.forEach(function (item) {
                    if (self.ruleForm.id != item.value) {
                        self.catList1.push({
                            label: item.label,
                            value: item.value,
                        })
                    }
                });
            },
            // 所有二级分类列表
            setCatList2() {
                let self = this;
                self.catList2 = [];
                self.options.forEach(function (item) {
                    if (self.ruleForm.id != item.value) {
                        self.catList2.push({
                            label: item.label,
                            value: item.value,
                            child: item.children ? item.children : null,
                        });
                    }
                });
            },
            // 所有三级分类列表
            setCatList3(list) {
                let self = this;
                self.catList3 = [];
                if (list) {
                    list.forEach(function (item) {
                        if (self.ruleForm.id != item.value) {
                            self.catList3.push({
                                label: item.label,
                                value: item.value,
                            });
                        }
                    });
                }
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/cat/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code === 0) {
                        self.ruleForm = e.data.data.detail;
                        self.handleData();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 分类 编辑时数据处理
            handleData() {
                let self = this;
                self.currentCatShow = {};
                self.parentIds = [];
                self.currentRadioKey = self.ruleForm.parents.length + 1;
                self.currentCatLevelShow = self.ruleForm.parents.length + 1;
                let parents = self.ruleForm.parents.reverse();
                parents.forEach(function (item, index) {
                    self.parentIds.push(item.id);
                    // 一级分类
                    if (index === 0) {
                        self.currentCatShow.label = item.name;
                        self.currentCatShow.value = item.id;
                    }
                    // 二级分类
                    if (index === 1) {
                        self.currentCatShow.oneChildren = {
                            label: item.name,
                            value: item.id
                        }
                    }
                })
            },
            changeParent(catLevel) {
                if (this.ruleForm.id) {
                    return false;
                }
                this.currentRadioKey = catLevel;
                if (catLevel !== 1) {
                    this.currentCatLevel = catLevel;
                    if (this.currentCatShow) {
                        this.currentCat = this.currentCatShow;
                    }
                    if (catLevel === 2) {
                        this.setCatList1();
                        this.dialogVisible1 = true;
                    }
                    if (catLevel === 3) {
                        this.setCatList2();
                        this.dialogVisible2 = true;
                    }
                } else {
                    this.currentCat = null;
                    this.currentCatShow = {};
                    this.parentIds = [];
                }
            },
            searchCat() {
                this.dialogLoading = true;
                this.getOptions();
            },
            // 选择分类 表格行点击事件
            tableRowClick(currentRow, oldCurrentRow) {
                if (currentRow) {
                    this.currentCat = currentRow;
                    if (this.currentCatLevel === 3) {
                        this.setCatList3(currentRow.child)
                    }
                }
            },
            tableRowClick2(currentRow, oldCurrentRow) {
                this.currentCat.oneChildren = currentRow;
            },
            // 选择分类 确认事件
            selectCatSubmit() {
                this.dialogVisible1 = false;
                this.dialogVisible2 = false;
                this.currentCatShow = this.currentCat;
                this.currentCatLevelShow = this.currentCatLevel;
                if (this.currentCatShow) {
                    this.parentIds = [];
                    this.parentIds.push(this.currentCatShow.value);
                    if (this.currentCatShow.oneChildren) {
                        this.parentIds.push(this.currentCatShow.oneChildren.value);
                    }
                }
                if (this.currentCatLevel == 3 && !this.currentCat.oneChildren) {
                    this.currentRadioKey = 2;
                    this.currentCatLevel = 2;
                    this.currentCatLevelShow = 2;
                }
            },
            tableRowClassName({row, rowIndex}) {
                if (this.currentCat && this.currentCat.value == row.value) {
                    return 'current-row';
                }

                return '';
            },
            tableRowClassName2({row, rowIndex}) {
                console.log(this.currentCat)
                console.log(row.value)
                if (this.currentCat && this.currentCat.value == row.value) {
                    this.currentCat.child = row.child;
                    this.catList3 = row.child;
                    return 'current-row';
                }

                return '';
            },
            tableRowClassName3({row, rowIndex}) {
                if (this.currentCat && this.currentCat.oneChildren
                    && this.currentCat.oneChildren.value == row.value) {
                    return 'current-row';
                }

                return '';
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getOptions();
        }
    });
</script>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .radio-box div {
        margin-right: 20px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0;
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
        padding: 0 10px;
    }

    .input-item .el-input-group__append .el-button {
        padding: 15px;
    }

    .el-dialog-1 .el-dialog {
        min-width: 350px;
    }

    .dialog-cat-box {
        border: 1px solid #E3E3E3;
        height: 250px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        width: 100%;
        margin-right: 10px;
        overflow-y: auto;
    }

    .el-dialog__body {
        padding: 10px 20px;
    }

    .el-table td, .el-table th {
        padding: 5px 0;
    }

    .cat-edit-text {
        color: #409EFF;
        margin-left: 10px;
        cursor: pointer;
    }

    .com-gallery .com-gallery-item {
        margin-bottom: 0;
        margin-right: 0;
        cursor: pointer;
    }

    .show-example {
        cursor: pointer;
        color: #409EFF;
    }
    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>