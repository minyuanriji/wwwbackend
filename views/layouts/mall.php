<?php
/**
 * @var \yii\web\View $this
 * @var string $content
 */
$isAdmin = false;
$isSuperAdmin = false;
if (!Yii::$app->admin->isGuest) {
    /** @var \app\models\Admin $admin */
    $admin = Yii::$app->admin->identity;
    if ($admin && $admin->admin_type == \app\models\Admin::ADMIN_TYPE_SUPER) {
        $isAdmin = true;
        $isSuperAdmin = true;
    }
    if ($admin && $admin->admin_type == \app\models\Admin::ADMIN_TYPE_ADMIN) {
        $isAdmin = true;
    }
}
try {
    $this->title = Yii::$app->mall->name;
} catch (Exception $exception) {
}
$currentRoute = Yii::$app->controller->route;
?>
<?php $this->beginPage(); ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
        <meta name="format-detection" content="telephone=no,email=no,address=no">
        <title><?= $this->title ? ($this->title . ' - ') : '' ?>商城管理</title>
        <link rel="stylesheet"
              href="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/theme-chalk/index.css">
        <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/flex.css">
        <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/common.css">
        <link href="//at.alicdn.com/t/font_353057_qq5xo4ymtf.css" rel="stylesheet">
        <link href="<?= Yii::$app->request->baseUrl ?>/../favicon.ico"
              mce_href="<?= Yii::$app->request->baseUrl ?>/../favicon.ico" rel="shortcut icon"/>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/jquery@3.3.1/dist/jquery.min.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue@2.6.10/dist/vue.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/index.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/qs@6.5.2/dist/qs.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/axios@0.18.0/dist/axios.min.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue-line-clamp@1.2.4/dist/vue-line-clamp.umd.js"></script>
        <script>
            let _layout = null;
            let _aside = null;
            const _csrf = '<?=Yii::$app->request->csrfToken?>';
            const _scriptUrl = '<?=Yii::$app->request->scriptUrl?>';
            const _baseUrl = '<?= \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl ?>';
            const _requestRoute = '<?=Yii::$app->requestedRoute?>';
            let _isWe7 = <?=is_we7() ? 'true' : 'false'?>;
            let _isInd = <?=is_we7() ? 'false' : 'true'?>;
            let _isAdmin = <?=$isAdmin ? 'true' : 'false'?>;
            let _isSuperAdmin = <?=$isSuperAdmin ? 'true' : 'false'?>;
        </script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/common.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/dayjs.min.js"></script>
        <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/echarts.min.js"></script>
        <script type="text/javascript" src="<?= Yii::$app->request->baseUrl ?>/statics/js/china.js"></script>
        <script type="text/javascript" src="<?= Yii::$app->request->baseUrl ?>/statics/js/world.js"></script>
        <script type="text/javascript" src="<?= Yii::$app->request->baseUrl ?>/statics/js/bmap.min.js"></script>

        <link href="//at.alicdn.com/t/font_1925214_nxjrzvwjd09.css" rel="stylesheet">
        <style>
            /* https://github.com/ElemeFE/element/pull/15359 */
            #_header{
                /*background-color: #24303c;*/
                background-color: #313131;
            }
            .el-input .el-input__count .el-input__count-inner {
                background: #FFF;
                display: inline-block;
                padding: 0 5px;
                line-height: normal;
            }

            html, body {
                height: 100%;
                padding: 0;
                margin: 0;
            }

            #app {
                height: 100%;
            }

            .el-header {
                padding: 0;
            }

            .el-container {
                height: 100%;
            }

            [v-cloak] {
                display: none !important;
            }

            input, textarea, select {
                appearance: none;
                outline: none !important;
                box-shadow: none;
            }

            .el-dialog {
                min-width: 600px;
            }

            /*新左侧菜单 start*/
            #_aside {
                position: relative;
            }

            .is-show-menu-2 {
                position: absolute;
                width: 30px;
                background: #F3F3F3;
                color: #A1A4A9;
                border-radius: 0 10px 10px 0;
                padding: 2px 8px;
                right: -30px;
                top: 17px;
                cursor: pointer;
                z-index: 10;
            }

            .menu-item {
                height: 60px;
                line-height: 60px;
                padding: 10px;
            }

            .left-menu {
                position: relative;
                width: 130px;
                height: 100%;
                overflow-y: auto;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }

            .menu-item-box.active {
                width: 100%;
                height: 100%;
                border-radius: 4px;
                background: #ebedf0;
                cursor: pointer;
            }

            /*一级菜单 start*/

            .aside-logo {
                height: 60px;
                width: 100%;
                /*background: #24303c;*/
                color: #f2f2f2;
                cursor: pointer;
                font-weight: bold;
                text-align: center;
                padding:0;
            }

            .aside-logo:hover {
                /*background: #30353a;*/
                /*color: #fff;*/
                /*background: #ffffff;*/
                /*color: #f2f2f2;*/
            }

            .aside-logo div {
                background: rgba(0, 0, 0, 0.5);
                padding: 6px 6px;
                width: 100%;
                border-radius: 3px;
            }

            .left-menu-1 {
                /*background: #24303c;*/
                background-color: #313131;
                cursor: pointer;
                width: 96px;
            }

            .menu-item-1 {
                color: #ffffff;
            }

            .menu-item-1.active {
                color: #000000;
                background: #ffffff;
            }

            .menu-item-1.hover {
                color: #FFFFFF;
                background: #666666;
                cursor: pointer;
            }

            .menu-item-1 .icon {
                margin-right: 5px;
            }

            /*一级菜单 end*/

            /*二级菜单 start*/

            .left-menu-2 .is-show-menu-1 {
                position: absolute;
                width: 30px;
                background: #F3F3F3;
                color: #A1A4A9;
                border-radius: 10px 0 0 10px;
                padding: 2px 8px;
                right: 0;
                top: 17px;
                cursor: pointer;
            }

            .menu-item-2 {
                cursor: pointer;
            }

            .menu-item-2.active {
                background: #edf6ff;
                color: #03c5ff;
            }

            .menu-item-2-title {
                color: #909399;
                border-bottom: 1px solid #E6E6E6;
                padding-left: 28px;
            }

            .menu-item-2:hover {
                color: #5DA8FC;
            }

            .menu-item-2 .icon-box {
                width: 14px;
                margin-right: 5px;
            }

            /*二级菜单 end*/

            /*三级菜单 start*/
            .menu-item-3 {
                cursor: pointer;
            }

            .menu-item-3.active {
                background: #edf6ff;
                color: #03c5ff;
            }

            .menu-item-3:hover {
                color: #03c5ff;
            }

            .menu-item-3 .icon-box {
                width: 14px;
                margin-right: 5px;
            }

            /*三级菜单 end*/

            /*定义元素最终移动到的位置，以及移动到此位置需要的时间*/
            .slide-enter-active, .slide-leave-active {
                transition: all .5s ease;
            }

            .slide-enter, .slide-leave-active {
                width: 0px !important;
            }

            .slide-leave, .slide-enter-active {
                width: 130px;
            }
        </style>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <div id="_layout"></div>
    <?= $this->renderFile('@app/views/components/index.php') ?>
    <div class="el-container">

        <div v-cloak id="_aside" flex="dir:left">
            <!--            <div @click="isShowMenu = true" v-if="!isShowMenu" class="is-show-menu-2">>></div>-->
            <div @click="isShowMenu = true" v-if="!isShowMenu" class="is-show-menu-2">
                <i class="el-icon-s-unfold"></i>
            </div>
            <!-- 一级菜单 -->
            <div class="left-menu left-menu-1">
                <div class="aside-logo" @click="indexClick" flex="main:center cross:center">
                    <template v-if="mall">
                        <!-- <div v-if="!overview" flex="main:center cross:center">{{mall.name}}</div>
                        <img v-else class="icon" src="statics/img/mall/data-screen/mall-logo.png" style="width: 38px;height: 38px;background-size: 100%;background-repeat: no-repeat"> -->
                        <img class="icon" src="statics/img/mall/data-screen/mall-logo.png" style="width:72%;max-height:50px;background-size:100%;background-repeat: no-repeat">
                    </template>
                </div>
                <div @click="menuClick1(leftMenu)"
                     v-for="leftMenu in leftMenus"
                     :key="leftMenu.id"
                     class="menu-item menu-item-1"
                     :class="{'active': currentMenu.opened_1 == leftMenu.id || currentMenu.temporary_opened_1 == leftMenu.id ? true : false,
                 'hover':currentMenu.temporary_opened_1 == leftMenu.id && currentMenu.opened_1 != leftMenu.id ? true : false }"
                     flex="dir:left cross:center">
                    <img class="icon" :src="leftMenu.id == currentMenu.opened_1 ? leftMenu.icon_active : leftMenu.icon" v-if="!leftMenu.icon_font">
                    <i :class="[leftMenu.icon_font?leftMenu.icon_font:'','icon']" v-if="leftMenu.icon_font"></i>
                    <com-ellipsis :line="1">{{leftMenu.name}}</com-ellipsis>
                </div>
            </div>
        </div>
        <div id="_layout_body" class="el-container is-vertical">
            <?php Yii::$app->loadComponentView('mall-header', __DIR__); ?>
            <div id="_header">
                <mall-header></mall-header>
            </div>
            <div id="_main" flex style="height: calc(100% - 60px);">

                <div v-cloak id="_aside_2">
                    <!-- 二级菜单 -->
                    <transition name="slide">
                        <div v-if="!_aside.overview && currentMenu.list && currentMenu.list.children && currentMenu.list.children.length > 0 && _aside.isShowMenu"
                             @mouseenter="_aside.mouseenterEvent2()"
                             @mouseleave="_aside.mouseleaveEvent2()"
                             class="left-menu left-menu-2"
                             :class="{'left-menu-2-show': !_aside.isShowMenu}"
                             style="width: 160px;">
                            <!-- 展示收起按钮 -->
                            <div @click="_aside.isShowMenu = false" v-if="_aside.isShowMenu" class="is-show-menu-1">
                                <i class="el-icon-s-fold"></i>
                            </div>
                            <div class="menu-item menu-item-2-title" flex="dir:left cross:center">
                                <com-ellipsis :line="1">{{currentMenu.list.name}}</com-ellipsis>
                            </div>
                            <div v-for="menu_1 in currentMenu.list.children"
                                 :key="menu_1.id"
                                 flex="dir:top">
                                <div @click="_aside.menuClick2(menu_1)"
                                     class="menu-item menu-item-2"
                                     :class="{'active': currentMenu.opened_2 == menu_1.id ? true : false}"
                                     flex="dir:left cross:center">
                                    <div class="menu-item-box"
                                         flex="dir:left cross:center">
                                        <div class="icon-box">
                                            <div v-if="menu_1.children">
                                                <i v-if="currentMenu.unfold_id_1 == menu_1.id" class="el-icon-arrow-down"></i>
                                                <i v-else class="el-icon-arrow-right"></i>
                                            </div>
                                        </div>
                                        <com-ellipsis :line="1">{{menu_1.name}}</com-ellipsis>
                                    </div>
                                </div>

                                <!-- 三级菜单 -->
                                <div v-if="currentMenu.unfold_id_1 == menu_1.id && menu_1.children"
                                     v-for="menu_2 in menu_1.children"
                                     :key="menu_2.id"
                                     flex="dir:top">
                                    <div @click="_aside.menuClick3(menu_2)"
                                         class="menu-item menu-item-3"
                                         :class="{'active': currentMenu.opened_3 == menu_2.id ? true : false}"
                                         flex="dir:left cross:center">
                                        <div class="menu-item-box"
                                             flex="dir:left cross:center">
                                            <div class="icon-box">
                                                <div v-if="menu_2.children">
                                                    <i v-if="currentMenu.unfold_id_2 == menu_2.id"
                                                       class="el-icon-arrow-down"></i>
                                                    <i v-else class="el-icon-arrow-right"></i>
                                                </div>
                                            </div>
                                            <com-ellipsis :line="1">{{menu_2.name}}</com-ellipsis>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
                <main class="el-main" style="background: #f3f3f3">
                    <?= $content ?>
                </main>
            </div>
        </div>

    </div>
    <script>
        //先加载时间过滤器
        Vue.filter('dateTimeFormat', function(value, key) {
            if (String(value).length !== 13) {
                value *= 1000;
            }
            let date = new Date(value);
            let Y = date.getFullYear();
            let m = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) ;
            let d = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
            let H = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours());
            let i = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes());
            let s = (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
            if (!value) return '暂无'
            switch (key) {
                case 'Y-m-d H:i:s':
                    return `${Y}-${m}-${d} ${H}:${i}:${s}`;
                case 'Y-m-d':
                    return `${Y}-${m}-${d}`;
                case 'Y-m':
                    return `${Y}-${m}`;
                case 'm-d':
                    return `${m}-${d}`;
                case 'Y':
                    return `${Y}`;
                case 'm':
                    return `${m}`;
                case 'd':
                    return `${d}`;
                case 'H:i:s':
                    return `${H}:${i}:${s}`;
                case 'H:i':
                    return `${H}:${i}`;
                case 'i:s':
                    return `${i}:${s}`;
                case 'H':
                    return `${H}`;
                case 'i':
                    return `${i}`;
                case 's':
                    return `${s}`;
                default:
                    return "格式错误";
            }
        })
        _layout = new Vue({
            el: '#_layout',
            created() {
                setInterval(() => {
                    this.$request({
                        params: {
                            r: 'keep-active/index',
                        },
                    }).then(e => {
                    }).catch(e => {
                    });
                }, 1000 * 60 * 5);
            },
        });
        _aside = new Vue({
            el: '#_aside',
            data() {
                return {
                    mall: null,
                    leftMenuLoading: false,
                    leftMenus: {},
                    defaultRoute: null,
                    currentMenu: {
                        list: null,
                        opened_1: 0,
                        temporary_opened_1: 0,
                        opened_2: 0,
                        opened_3: 0,
                        unfold_id_1: 0,
                        unfold_id_2: 0,
                    },
                    currentRoute: "<?= $currentRoute ?>",
                    isShowMenu: true,
                    overview: false
                };
            },
            methods: {
                getMenus() {
                    const cacheKey = '_MALL_MENUS';
                    let data = localStorage.getItem(cacheKey);
                    if (data) {
                        try {
                            data = JSON.parse(data);
                        } catch (e) {
                            data = false;
                        }
                    }
                    if (data && data.menus) {
                        this.leftMenus = data.menus;
                    } else {
                        this.leftMenuLoading = true;
                    }
                    this.currentMenu.opened_1 = localStorage.getItem('_OPENED_MENU_1_ID');
                    this.currentMenu.opened_2 = localStorage.getItem('_OPENED_MENU_2_ID');
                    this.currentMenu.opened_3 = localStorage.getItem('_OPENED_MENU_3_ID');
                    this.currentMenu.unfold_id_1 = localStorage.getItem('_UNFOLD_ID_1');
                    this.currentMenu.unfold_id_2 = localStorage.getItem('_UNFOLD_ID_2');
                    this.setMenus();
                    let self = this;
                    this.$request({
                        params: {
                            r: 'mall/menus/index',
                        },
                        method: 'post',
                        data: {
                            route: getQuery('r')
                        }
                    }).then(e => {
                        localStorage.setItem(cacheKey, JSON.stringify(e.data.data));
                        self.leftMenuLoading = false;
                        self.leftMenus = e.data.data.menus;
                        self.leftMenus.forEach(function (item) {
                            if (item.is_active) {
                                self.currentMenu.opened_1 = item.id;
                                localStorage.setItem('_OPENED_MENU_1_ID', self.currentMenu.opened_1);
                                if (item.children) {
                                    item.children.forEach(function (cItem1) {
                                        if (cItem1.is_active) {
                                            if (cItem1.children) {
                                                self.currentMenu.unfold_id_1 = cItem1.id;
                                                localStorage.setItem('_UNFOLD_ID_1', self.currentMenu.unfold_id_1);
                                                cItem1.children.forEach(function (cItem2) {
                                                    if (cItem2.is_active) {
                                                        if (cItem2.children) {
                                                            self.currentMenu.unfold_id_2 = cItem2.id;
                                                            localStorage.setItem('_UNFOLD_ID_2', self.currentMenu.unfold_id_2);
                                                            cItem2.children.forEach(function (cItem3) {
                                                                if (cItem3.is_active) {
                                                                    self.currentMenu.opened_2 = cItem3.id;
                                                                    localStorage.setItem('_OPENED_MENU_2_ID', self.currentMenu.opened_2);
                                                                }
                                                            })
                                                        } else {
                                                            self.currentMenu.opened_2 = cItem2.id;
                                                            localStorage.setItem('_OPENED_MENU_2_ID', self.currentMenu.opened_2);
                                                        }
                                                    }
                                                })
                                            } else {
                                                self.currentMenu.opened_2 = cItem1.id;
                                                localStorage.setItem('_OPENED_MENU_2_ID', self.currentMenu.opened_2);
                                            }
                                        }
                                    })
                                }
                            }
                        });
                        self.setMenus();
                    }).catch(e => {
                        console.log(e);
                    });
                },
                openUrl(menu) {
                    localStorage.setItem('_UNFOLD_ID_1', this.currentMenu.unfold_id_1);
                    localStorage.setItem('_UNFOLD_ID_2', this.currentMenu.unfold_id_2);
                    if (menu) {
                        let args = {
                            r: menu.route
                        };
                        if (menu.params) {
                            for (let i in menu.params) {
                                args[i] = menu.params[i];
                            }
                        }


                        navigateTo(args)
                        //}
                    }
                },
                setMenus() {
                    let self = this;
                    if (!self.currentMenu.opened_2 && !self.currentMenu.unfold_id_1) {
                        self.currentMenu.opened_1 = 0;
                    }

                    if (self.leftMenus && self.leftMenus.length > 0) {
                        self.leftMenus.forEach(function (item) {
                            if (item.id == self.currentMenu.opened_1) {
                                self.currentMenu.list = item;
                            }
                        });
                    }
                },
                // 点击一级菜单
                menuClick1(menu) {
                    this.clearMenuStorage();
                    this.currentMenu.opened_1 = menu.id;
                    this.currentMenu.list = menu;
                    localStorage.setItem('_OPENED_MENU_1_ID', menu.id);
                    if (!menu.children) {


                        this.openUrl(menu);
                    } else {
                        if (menu.key == 'app-manage') {
                            return
                        }
                        if (menu.children[0].children && menu.children[0].children.length > 0) {


                            this.openUrl(menu.children[0].children[0]);

                            localStorage.setItem('_UNFOLD_ID_1', menu.children[0].id);
                            localStorage.setItem('_OPENED_MENU_3_ID', menu.children[0].children[0].id);
                        } else {


                            this.openUrl(menu.children[0]);
                            localStorage.setItem('_OPENED_MENU_2_ID', menu.children[0].id);
                        }
                    }
                },
                // 点击二级菜单
                menuClick2(menu) {
                    if (menu.children) {
                        let unfoldId1 = null;
                        if (this.currentMenu.unfold_id_1 == menu.id) {
                            unfoldId1 = 0;
                        } else {
                            unfoldId1 = menu.id;
                        }
                        this.currentMenu.unfold_id_1 = unfoldId1;
                    } else {
                        this.currentMenu.opened_2 = menu.id;
                        let temporary = this.currentMenu.temporary_opened_1;
                        if (temporary) {
                            localStorage.setItem('_OPENED_MENU_1_ID', temporary);
                        }
                        localStorage.setItem('_OPENED_MENU_2_ID', menu.id);
                        localStorage.setItem('_OPENED_MENU_3_ID', 0);
                        this.openUrl(menu);
                    }
                },
                // 点击三级菜单
                menuClick3(menu) {
                    if (menu.children) {
                        let unfoldId2 = null;
                        if (this.currentMenu.unfold_id_2 == menu.id) {
                            unfoldId2 = 0;
                        } else {
                            unfoldId2 = menu.id;
                        }
                        this.currentMenu.unfold_id_2 = unfoldId2;
                    } else {
                        this.currentMenu.opened_3 = menu.id;
                        let temporary = this.currentMenu.temporary_opened_1;
                        if (temporary) {
                            localStorage.setItem('_OPENED_MENU_1_ID', temporary);
                        }
                        localStorage.setItem('_OPENED_MENU_2_ID', 0);
                        localStorage.setItem('_OPENED_MENU_3_ID', menu.id);
                        this.openUrl(menu);
                    }
                },
                indexClick() {
                    navigateTo({r: 'mall/overview/index'})
                    this.clearMenuStorage();
                },
                clearMenuStorage() {
                    localStorage.removeItem('_OPENED_MENU_1_ID');
                    localStorage.removeItem('_OPENED_MENU_2_ID');
                    localStorage.removeItem('_OPENED_MENU_3_ID');
                    localStorage.removeItem('_UNFOLD_ID_1');
                    localStorage.removeItem('_UNFOLD_ID_2');
                },
                mouseenterEvent2() {
                    let self = this;
                    if (self.currentMenu.temporary_opened_1 > 0) {
                        self.leftMenus.forEach(function (item) {
                            if (self.currentMenu.temporary_opened_1 === item.id) {
                                self.currentMenu.list = item;
                            }
                        })
                    }
                },
                mouseleaveEvent2() {
                    let self = this;
                    self.currentMenu.temporary_opened_1 = 0;
                    self.leftMenus.forEach(function (item) {
                        if (item.id === self.currentMenu.opened_1) {
                            self.currentMenu.list = item;
                        }
                    });
                    self.currentMenu.unfold_id_1 = localStorage.getItem('_UNFOLD_ID_1');
                    self.currentMenu.unfold_id_2 = localStorage.getItem('_UNFOLD_ID_2');
                    if (this.currentMenu.opened_1 <= 0) {
                        this.currentMenu.list = {};
                    }
                },
            },
            mounted: function () {
                this.getMenus();
                if(getQuery('r') == 'mall/overview/index'){
                    $('.left-menu-1').css('width','96');
                    $('.left-menu-1').css('background-color','#313131');
                    $('.el-main').css('padding',0);
                    this.overview = true;
                    setTimeout(() => {
                        $('.el-card, .el-message').css('border-radius',0)
                    },1000)
                }
            }
        });
        _header = new Vue({el: '#_header'});

        _main = new Vue({
            el: '#_aside_2',
            data(){
                return {
                    currentMenu: _aside.currentMenu,
                }
            }
        })

    </script>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>