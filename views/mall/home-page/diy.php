<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */


$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$mallComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $mallComponents[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($mallComponents as $component) {
    Yii::$app->loadComponentView("diy-{$component}", $diyPath);
}
$currentDir = opendir(__DIR__);
$diyComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $temp = substr($file, 4, (stripos($file, '.php') - 4));
        if (!in_array($temp, $mallComponents)) {
            $diyComponents[] = $temp;
        }
    }
}
closedir($currentDir);
foreach ($diyComponents as $component) {
    Yii::$app->loadComponentView("diy-{$component}", __DIR__);
}
$components = array_merge($diyComponents, $mallComponents);
Yii::$app->loadComponentView('com-hotspot');
Yii::$app->loadComponentView('com-rich-text');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="width: 100%" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" style="justify-content: space-between">
            <span>首页装修</span>
            <div style="float: right; margin: -5px 0">
                <el-button size="small" @click="save" type="primary" :loading="submitLoading">保存设置</el-button>
            </div>
        </div>

        <div v-loading="loading">
            <div flex="box:first" style="margin-bottom: 10px;min-width: 1280px;height: 725px;background-color: #ffffff;">
                <div class="all-components">
                    <div class="component-group" v-for="group in allComponents">
                        <div class="component-group-name">{{group.groupName}}</div>
                        <div class="component-list" flex="">
                            <template v-for="item in group.list">
                                <div class="component-item" @click="selectComponent(item)">
                                    <img class="component-icon" :src="item.icon">
                                    <div class="component-name">{{item.name}}</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div style="padding-left: 2px;position: relative;overflow-y: auto">
                    <div style="overflow-y: auto;padding: 0 25px;width: 435px;height: 705px;">
                        <div class="mobile-framework" style="height: 705px;">
                            <div class="mobile-framework-header"></div>
                            <div class="mobile-framework-body">
                                <draggable v-model="components" :options="{filter:'.active',preventOnFilter:false}"
                                           v-if="components && components.length">
                                    <div v-for="(component, index) in components" :key="component.key"
                                         @click="showComponentEdit(component,index)"
                                         :class="(component.active?'active':'')">
                                        <div class="diy-component-options" v-if="component.active">
                                            <el-button type="primary"
                                                       icon="el-icon-delete"
                                                       @click.stop="deleteComponent(index)"
                                                       style="left: -25px;top:0;"></el-button>
                                            <el-button type="primary"
                                                       icon="el-icon-document-copy"
                                                       @click.stop="copyComponent(index)"
                                                       style="left: -25px;top:30px;"></el-button>
                                            <el-button v-if="index > 0 && components.length > 1"
                                                       type="primary"
                                                       icon="el-icon-arrow-up"
                                                       @click.stop="moveUpComponent(index)"
                                                       style="right: -25px;top:0;"></el-button>
                                            <el-button v-if="components.length > 1 && index < components.length-1"
                                                       type="primary"
                                                       icon="el-icon-arrow-down"
                                                       @click.stop="moveDownComponent(index)"
                                                       style="right: -25px;top:30px;"></el-button>
                                        </div>
                                        <?php foreach ($components as $component) : ?>
                                            <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                                                   :active="component.active"
                                                                   v-model="component.data"></diy-<?= $component ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </draggable>
                                <div v-else flex="main:center cross:center"
                                     style="height: 200px;color: #adb1b8;text-align: center;">
                                    <div>
                                        <i class="el-icon-folder-opened" style="font-size: 32px;margin-bottom: 10px"></i>
                                        <div>空空如也</div>
                                        <div>请从左侧组件库添加组件</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </el-card>

</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.18.1/vuedraggable.umd.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                allComponents: [],
                id: null,
                components: [],
                submitLoading: false,
                model: '',
                overrun: null
            };
        },
        created() {
            this.id = getQuery('id');
            this.model = getQuery('model');
            this.loadData();
        },

        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'mall/home-page/diy',
                        id: this.id,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.allComponents = response.data.data.allComponents;
                        if (response.data.data.components) {
                            this.components = (response.data.data.components);
                        }
                    } else {
                    }
                }).catch(e => {
                });
            },
            refreshComponents() {

                this.components.forEach((item, index) => {
                    if (item.data && item.data.is_last) {
                        this.components.splice(index, 1);
                        this.components.push(item);
                    }
                })
            },

            selectComponent(e) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length >= this.overrun.diy_module_overrun) {
                    this.$message.error('最多添加' + this.overrun.diy_module_overrun + '个组件');
                    return;
                }
                if (e.single) {
                    for (let i in this.components) {
                        if (this.components[i].id === e.id) {
                            this.$message.error('该组件只允许添加一个。');
                            return;
                        }
                    }
                }
                let currentIndex = this.components.length;
                for (let i in this.components) {
                    if (this.components[i].active) {
                        currentIndex = i + 1;
                        break;
                    }
                }
                const component = {
                    id: e.id,
                    data: null,
                    active: false,

                    key: randomString(),
                };
                // 做一层非空判断
                if(currentIndex>0){
                    this.components.splice(currentIndex, 0, component);
                    this.refreshComponents();
                }else{
                    let componentsArr = [];
                    componentsArr.push(component);
                    this.components = componentsArr;
                    this.refreshComponents();
                }
            },
            showComponentEdit(component, index) {
                for (let i in this.components) {
                    if (i == index) {
                        this.components[i].active = true;
                    } else {
                        this.components[i].active = false;
                    }
                }
                this.$forceUpdate();
            },
            deleteComponent(index) {
                this.components.splice(index, 1);
                this.refreshComponents();
            },
            copyComponent(index) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length >= this.overrun.diy_module_overrun) {
                    this.$message.error('最多添加' + this.overrun.diy_module_overrun + '个组件');
                    return;
                }
                for (let i in this.allComponents) {
                    for (let j in this.allComponents[i].list) {

                        if (this.allComponents[i].list[j].id === this.components[index].id) {
                            if (this.allComponents[i].list[j].single) {
                                this.$message.error('该组件只允许添加一个。');
                                return;
                            }
                        }
                    }
                }
                let json = JSON.stringify(this.components[index]);
                let copy = JSON.parse(json);
                copy.active = false;
                copy.key = randomString();
                this.components.splice(index + 1, 0, copy);
                this.refreshComponents();
            },
            moveUpComponent(index) {
                this.swapComponents(index, index - 1);
                this.refreshComponents();
            },
            moveDownComponent(index) {
                this.swapComponents(index, index + 1);
            },
            swapComponents(index1, index2) {
                this.components[index2] = this.components.splice(index1, 1, this.components[index2])[0];
                this.refreshComponents();
            },
            save() {
                //   this.submitLoading = true;
                this.refreshComponents();

                const postComponents = [];
                for (let i in this.components) {
                    postComponents.push({
                        id: this.components[i].id,
                        data: this.components[i].data,
                    });
                }
				
				
                this.$request({
                    params: {
                        r: 'mall/home-page/diy',
                    },
                    method: 'post',
                    data: {
                        page_data: JSON.stringify(postComponents),
                    },
                }).then(response => {
                    //  this.submitLoading = false;
                    if (response.data.code === 0) {
						console.log(postComponents);
						console.log(888888888888);
                        this.$message.success(response.data.msg);
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(e => {
                });
            },

        },
    });
</script>
<style>
    .all-components {
        background: #fff;
        padding: 20px;
    }

    .all-components .component-group {
        border: 1px solid #eeeeee;
        width: 300px;
        margin-bottom: 20px;
    }

    .all-components .component-group:last-child {
        margin-bottom: 0;
    }

    .all-components .component-group-name {
        height: 35px;
        line-height: 35px;
        background: #f7f7f7;
        padding: 0 20px;
        border-bottom: 1px solid #eeeeee;
    }

    .all-components .component-list {
        margin-right: -2px;
        margin-top: -2px;
        flex-wrap: wrap;
    }

    .all-components .component-list .component-item {
        width: 100px;
        height: 100px;
        border: 0 solid #eeeeee;
        border-width: 0 1px 1px 0;
        text-align: center;
        padding: 15px 0 0;
        cursor: pointer;
    }

    .all-components .component-list .component-icon {
        width: 40px;
        height: 40px;
        /*border: 1px solid #eee;*/
    }

    .all-components .component-list .component-name {

    }

    .mobile-framework {
        width: 375px;
        height: 100%;
    }

    .mobile-framework-header {
        height: 60px;
        line-height: 60px;
        background: #333;
        color: #fff;
        text-align: center;
        background: url('statics/img/mall/head.png') no-repeat;
    }

    .mobile-framework-body {
        min-height: 645px;
        border: 1px solid #e2e2e2;
        background: #f5f7f9;
    }

    .mobile-framework .diy-component-preview {
        cursor: pointer;
        position: relative;
        zoom: 0.5;
        -moz-transform: scale(0.5);
        -moz-transform-origin: top left;
        font-size: 28px;
    }

    @-moz-document url-prefix() {
        .mobile-framework .diy-component-preview {
            cursor: pointer;
            position: relative;
            -moz-transform: scale(0.5);
            -moz-transform-origin: top left;
            font-size: 28px;
            width: 200% !important;
            height: 100%;
            margin-bottom: auto;
        }
        .mobile-framework .active .diy-component-preview {
            border: 2px dashed #409EFF;
            left: -2px;
            right: -2px;
            width: calc(200% + 4px) !important;
        }
    }

    .mobile-framework .diy-component-preview:hover {
        box-shadow: inset 0 0 10000px rgba(0, 0, 0, .03);
    }

    .mobile-framework .diy-component-edit {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 465px;
        right: 0;
        background: #fff;
        padding: 20px;
        display: none;
        overflow: auto;
    }

    .diy-component-options {
        position: relative;
    }

    .diy-component-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .mobile-framework .active .diy-component-preview {
        border: 2px dashed #409EFF;
        left: -2px;
        right: -2px;
        width: calc(100% + 4px);
    }

    .mobile-framework .active .diy-component-edit {
        display: block;
        padding-right: 20%;
        min-width: 650px;
    }

    .all-components {
        max-height: 725px;
        overflow-y: auto;
    }

    .bottom-menu {
        text-align: center;
        height: 54px;
        width: 100%;
    }

    .bottom-menu .el-card__body {
        padding-top: 10px;
    }

    .el-dialog {
        min-width: 800px;
    }
</style>