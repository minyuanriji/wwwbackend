<template id="area-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加区域代理">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="用户昵称">
                    <el-autocomplete size="small" v-model="edit.nickname" value-key="nickname"
                                     @keyup.enter.native="keyUp"
                                     :fetch-suggestions="querySearchAsync" placeholder="请输入用户昵称"
                                     @select="areaClick"></el-autocomplete>
                </el-form-item>
            </el-form>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="等级">
                    <el-select v-model="level" placeholder="请选择区域代理等级" @change="levelChange">
                        <el-option
                                v-for="item in level_list"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-form>


            <template v-if="level>0">


                <el-form @submit.native.prevent size="small" label-width="150px">
                    <el-form-item label="省市区" prop="address">
                        <el-cascader
                                @change="addressChange"
                                :options="district"
                                :props="props"
                                v-model="address">
                        </el-cascader>
                    </el-form-item>
                </el-form>
                <el-form @submit.native.prevent size="small" label-width="150px" v-if="level==1">
                    <el-form-item label="镇">
                        <el-select v-model="town_id" placeholder="请选择镇">
                            <el-option
                                    v-for="item in town_list"
                                    :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
            </template>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small"
                       @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('area-edit', {
        template: '#area-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                edit: {
                    visible: false,
                    keyword: '',
                    nickname: '',
                    id: '',
                    btnLoading: false,
                },
                district: [],
                address: null,
                town_list: [],
                province_id: 0,
                city_id: 0,
                district_id: 0,
                town_id: '',
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                level_list: [
                    {
                        name: '省代',
                        level: 4
                    },
                    {
                        name: '市代',
                        level: 3
                    },
                    {
                        name: '区代',
                        level: 2
                    },
                    {
                        name: '镇代',
                        level: 1
                    },
                ],
                level: ''
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                } else {
                    this.edit.id = '';
                    this.edit.nickname = '';
                    this.edit.keyword = '';
                    this.edit.visible = false;
                }
            },
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
            }
        },
        created() {
            this.getDistrict(1);
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
                    console.log(e);
                    if (e.data.code == 0) {
                        console.log(e.data.list);
                        this.town_list = e.data.list;
                        console.log(this.town_list);
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
            querySearchAsync(queryString, cb) {
                this.edit.keyword = queryString;
                this.get_user(cb);
            },
            get_user(cb) {
                request({
                    params: {
                        r: 'plugin/area/mall/area/search-user',
                        keyword: this.edit.keyword
                    }
                }).then(res => {
                    if (res.data.code == 0) {
                        cb(res.data.data.list)
                    } else {
                        this.$message.error(res.data.msg);
                    }
                });
            },
            areaClick(row) {
                this.edit.id = row.id
            },

            editCancel() {
                this.$emit('input', false);
                navigateTo({
                    r: 'plugin/area/mall/area/index',
                })
            },
            editSave() {
                this.edit.btnLoading = true;
                if (this.level == 1) {
                    if (this.town_id == '' || this.town_id == undefined) {
                        this.$message.error('请选择镇');
                        return;
                    }
                }
                this.address.push(parseInt(this.town_id));
                if (this.level == 1) {
                    this.province_id = this.address[0];
                    this.city_id = this.address[1];
                    this.district_id = this.address[2];
                    this.town_id = this.address[3];
                }
                if (this.level == 2) {
                    this.province_id = this.address[0];
                    this.city_id = this.address[1];
                    this.district_id = this.address[2];
                    this.town_id = 0;
                }
                if (this.level == 3) {
                    this.province_id = this.address[0];
                    this.city_id = this.address[1];
                    this.district_id = 0;
                    this.town_id = 0;
                }
                if (this.level == 4) {
                    this.province_id = this.address[0];
                    this.city_id = 0;
                    this.district_id = 0;
                    this.town_id = 0;
                }

                request({
                    params: {
                        r: 'plugin/area/mall/area/edit',
                    },
                    method: 'post',
                    data: {
                        id: this.edit.id,
                        level: this.level,
                        province_id: this.province_id,
                        city_id: this.city_id,
                        district_id: this.district_id,
                        town_id: this.town_id
                    }
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('添加成功');
                        this.editCancel();
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            keyUp() {
                console.log('key up')
            }
        }
    });
</script>
