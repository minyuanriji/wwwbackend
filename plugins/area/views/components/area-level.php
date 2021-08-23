<template id="area-level">
    <el-dialog :visible.sync="edit.visible" width="20%" title="修改区域代理等级">
        <div v-loading="edit.loading">
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
                <!--<el-form @submit.native.prevent size="small" label-width="150px" v-if="level==1">
                    <el-form-item label="镇">
                        <el-select v-model="town_id" placeholder="请选择镇">
                            <el-option
                                    v-for="item in town_list"
                                    :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-form>-->
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
    Vue.component('area-level', {
        template: '#area-level',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            area: Object,
        },
        data() {
            return {
                edit: {
                    visible: false,
                    level: '',
                    btnLoading: false,
                    list: [],
                    loading: false,
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
                    /*{
                        name: '镇代',
                        level: 1
                    },*/
                ],
                level: ''
            };
        },
        watch: {
            value() {
                if (this.value) {
                    console.log(this.area,'this.area');
                    this.edit.visible = true;
                    this.level = this.area.level;
                    this.getDistrict(this.area.level);
                    
                    switch(this.area.level) {
                        case 4:
                            this.address = [this.area.province_id];
                            break;
                        case 3:
                            this.address = [this.area.province_id,this.area.city_id];
                            break;
                        default:
                            this.address = [this.area.province_id,this.area.city_id,this.area.district_id];
                            this.getTownList(this.area.district_id);
                    }
                    this.town_id = this.area.town;
                } else {
                    this.edit.id = '';
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
            addressChange(e) { //选择省市区
                this.town_list = []
                this.town_id = '';

                if (e.length == 3) {
                    this.getTownList(e[2]);
                }
            },
            getTownList(district_id) { //如果省市区有区，则请求街道数据
                request({
                    params: {
                        r: 'district/town-list',
                        district_id: district_id
                    },
                }).then(e => {
                    console.log(e,'this.town_list222');
                    if (e.data.code == 0) {
                        this.town_list = e.data.list;
                        console.log(this.town_list,'this.town_list');
                    }
                }).catch(e => {
                });

            },
            levelChange(e) {
                this.getDistrict(e);
            },
            // 获取省市区列表
            getDistrict(level) { //请求省市区数据
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
                    console.log(e,'eeeeeeee');
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            editCancel() {
                this.$emit('input', false);
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
                if (this.address.length == 0) {
                    this.$message.error('请选择地区');
                    return;
                }
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
                console.log(this.address,'addressaddress')
                console.log(this.district_id);
                request({
                    params: {
                        r: 'plugin/area/mall/area/level-change',
                    },
                    method: 'post',
                    data: {
                        level: this.level,
                        id: this.area.user_id,
                        province_id: this.province_id,
                        city_id: this.city_id,
                        district_id: this.district_id,
                        town_id: this.town_id
                    }
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('修改成功');
                        this.editCancel();
                        this.$emit('success', true);
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            }
        }
    });
</script>

