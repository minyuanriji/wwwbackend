<div id="app">
    <a-layout>
        <a-layout-header class="l-header">header</a-layout-header>
        <a-layout-content class="main-con">
            <template>
                <a-form
                        id="components-form-demo-normal-login"
                        :form="form"
                        class="login-form"
                        @submit="handleSubmit"
                >
                    <a-form-item>
                        <a-input
                                v-decorator="[
          'userName',
          { rules: [{ required: true, message: 'Please input your username!' }] },
        ]"
                                placeholder="用户名"
                        >
                            <a-icon slot="prefix" type="user" style="color: rgba(0,0,0,.25)" />
                        </a-input>
                    </a-form-item>
                    <a-form-item>
                        <a-input
                                v-decorator="[
          'password',
          { rules: [{ required: true, message: 'Please input your Password!' }] },
        ]"
                                type="password"
                                placeholder="密码"
                        >
                            <a-icon slot="prefix" type="lock" style="color: rgba(0,0,0,.25)" />
                        </a-input>
                    </a-form-item>
                    <a-form-item>
                        <a-checkbox
                                v-decorator="[
          'remember',
          {
            valuePropName: 'checked',
            initialValue: true,
          },
        ]"
                        >
                            记住我
                        </a-checkbox>
                        <a-button type="primary" html-type="submit" class="login-form-button">
                            系统登陆
                        </a-button>
                    </a-form-item>
                </a-form>
            </template>
        </a-layout-content>
        <a-layout-footer class="l-footer">Footer</a-layout-footer>
    </a-layout>
</div>
<style>
body{}
.main-con{margin-left:35%;width:30%;}

.ant-layout-header{
    background:#f0f2f5
}
</style>
<script>
var vue = new Vue({
    el: '#app',
    data() {
        return {

        }
    },
    beforeCreate() {
        this.form = this.$form.createForm(this, { name: 'normal_login' });
    },
    methods: {
        handleSubmit(e) {
            e.preventDefault();
            this.form.validateFields((err, values) => {
                if (!err) {
                    console.log('Received values of form: ', values);
                }
            });
        },
    }
});
</script>