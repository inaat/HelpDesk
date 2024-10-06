<template>
    <div>
<!--        <h1 class="mb-8 font-bold text-3xl">-->
<!--            <Link class="text-indigo-400 hover:text-indigo-600" :href="this.route('tickets')">{{ __('Tickets') }}</Link>-->
<!--            <span class="text-indigo-400 font-medium">/</span> {{ __('Create') }}-->
<!--        </h1>-->
        <div class="bg-white rounded-md shadow overflow-hidden max-w-full">
            <form @submit.prevent="store">
                <div class="p-8 -mr-6 -mb-8 flex flex-wrap">


                    <select-input-filter placeholder="Start typing" :onInput="doFilter" :items="customers"
                                         v-if="user_access.ticket.update && auth.user.role.slug !== 'customer'"
                                         v-model="form.user_id" :error="form.errors.user_id"
                                         class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Customer')">
                    </select-input-filter>


                    <select-input v-if="user_access.ticket.update" v-model="form.priority_id" :error="form.errors.priority_id" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Priority')">
                        <option :value="null" />
                        <option v-for="s in priorities" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select-input>
                    <select-input v-if="!(hidden_fields && hidden_fields.includes('ticket_type'))" v-model="form.type_id" :error="form.errors.type_id" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Ticket Type')">
                        <option :value="null" />
                        <option v-for="s in types" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select-input>

                    <select-input v-if="!(hidden_fields && hidden_fields.includes('department'))" @change="getCategories()" v-model="form.department_id" :error="form.errors.department_id" class="pr-6 pb-5 md:col-span-6 lg:w-1/3" :label="__('Department')">
                        <option :value="null">{{ __('Select a department') }}</option>
                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                    </select-input>

                    <select-input ref="category" v-if="!(hidden_fields && hidden_fields.includes('category')) && form.department_id" @change="getSubCategories()" v-model="form.category_id" :error="form.errors.category_id" class=" pb-5 pr-6 md:col-span-6 lg:w-1/3" :label="__('Category')">
                        <option :value="null">{{ __('Select a category') }}</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select-input>

                    <select-input ref="sub_category"  v-if="form.category_id" v-model="form.sub_category_id" :error="form.errors.sub_category_id" class=" pb-5 md:col-span-6 lg:w-1/3" :label="__('Sub Category')">
                        <option :value="null">{{ __('Select a sub category') }}</option>
                        <option v-for="category in sub_categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select-input>

                    <select-input-filter placeholder="Start typing" :onInput="doFilterUsersExceptCustomer" :items="usersExceptCustomers"
                                         v-if="auth.user.role.slug !== 'customer' && user_access.ticket.update && !(hidden_fields && hidden_fields.includes('assigned_to'))"
                                         v-model="form.assigned_to" :error="form.errors.assigned_to"
                                         class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Assigned to')">
                    </select-input-filter>

                    <text-input v-model="form.subject" :error="form.errors.subject" class="pr-6 pb-8 w-full lg:w-full" :label="__('Subject')" />
                    <div class="pr-6 pb-8 w-full">
                        <label class="form-label" >Request Details:</label>
                        <ckeditor id="ticketDetails" :editor="editor" v-model="form.details" :config="editorConfig"></ckeditor>
                    </div>
                    <input ref="file" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf, .zip" class="hidden" multiple="multiple" @change="fileInputChange" />
                    <div class="pr-6 pb-8 w-full lg:w-full flex-col">
                        <button type="button" class="btn flex justify-center items-center" @click="fileBrowse">
                            <icon name="file" class="flex-shrink-0 h-8 fill-gray-400 pr-1" /> <h4>{{ __('Attach files') }}</h4>
                        </button>
                        <div v-if="form.files.length" class="flex items-center justify-between pr-6 pt-8 w-full lg:w-1/2" v-for="(file, fi) in form.files" :key="fi">
                            <div class="flex-1 pr-1">
                                {{ file.name }} <span class="text-gray-500 text-xs">({{ getFileSize(file.size) }})</span>
                            </div>
                            <button type="button" class="btn flex justify-center items-center" @click="fileRemove(file, fi)">
                                {{ __('Remove') }}</button>
                        </div>
                    </div>
                </div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end items-center">
                    <loading-button :loading="form.processing" class="btn-indigo" type="submit">{{ __('Create') }}
                        {{ __('Ticket') }}</loading-button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import SelectInputFilter from '@/Shared/SelectInputFilter'
import TextareaInput from '@/Shared/TextareaInput'
import LoadingButton from '@/Shared/LoadingButton'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import UploadAdapter from '@/Shared/UploadAdapter';
import CKEditor from '@ckeditor/ckeditor5-vue';
import axios from 'axios'

export default {
    metaInfo: { title: 'Create Ticket' },
    components: {
        LoadingButton,
        SelectInput,
        SelectInputFilter,
        TextInput,
        TextareaInput,
        Link,
        Icon,
        ckeditor: CKEditor.component,
    },
    layout: Layout,
    props: {
        customers: Array,
        usersExceptCustomers: Array,
        priorities: Array,
        statuses: Array,
        types: Array,
        departments: Array,
        all_categories: Array,
        auth: Object,
        hidden_fields: Object,
    },
    remember: false,
    data() {
        return {
            user_access: this.$page.props.auth.user.access,
            editor: ClassicEditor,
            editorConfig: {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'insertTable', 'blockQuote', '|', 'imageUpload', 'mediaEmbed', '|', 'undo', 'redo' ],
                table: {
                    toolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                },
                extraPlugins: [this.uploader],
            },
            categories: [],
            sub_categories: [],
            form: this.$inertia.form({
                user_id: null,
                priority_id: null,
                status_id: null,
                department_id: null,
                category_id: null,
                sub_category_id: null,
                assigned_to: null,
                type_id: null,
                subject: null,
                details: '',
                files: [],
            }),
        }
    },
    created() {
        this.setDefaultValue(this.statuses, 'status_id', 'Pending')
        this.setDefaultValue(this.priorities, 'priority_id', 'Generally')
    },
    methods: {
        getCategories(){
            this.categories = this.all_categories.filter(cat=>cat.department_id === this.form.department_id)
            this.form.category_id = null;
            this.$refs.category.selected = null;
        },
        getSubCategories(){
            this.sub_categories = this.all_categories.filter(cat=>cat.parent_id === this.form.category_id)
            this.form.sub_category_id = null;
            this.$refs.sub_category.selected = null;
        },
        uploader(editor) {
            editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
                return new UploadAdapter( loader );
            };
        },
        doFilter(e){
            axios.get(this.route('filter.customers', {search: e.target.value})).then((res)=>{
                this.customers.splice(0, this.customers.length, ...res.data);
            })
        },
        doFilterUsersExceptCustomer(e){
            axios.get(this.route('filter.users_except_customer', {search: e.target.value})).then((res)=>{
                this.usersExceptCustomers.splice(0, this.usersExceptCustomers.length, ...res.data);
            })
        },
        setDefaultValue(arr, key, value){
            const find = arr.find(i=>i.name.match(new RegExp(value + ".*")))
            if(find){
                this.form[key] = find['id']
            }
        },
        fileInputChange(e) {
            let selectedFiles = e.target.files;
            for (let i = 0; i < selectedFiles.length; i++) {
                this.form.files.push(selectedFiles[i]);
            }
        },
        fileRemove(image, index) {
            this.form.files.splice(index, 1);
        },
        getFileSize(size) {
            const i = Math.floor(Math.log(size) / Math.log(1024))
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i]
        },
        fileBrowse() {
            this.$refs.file.click()
        },
        store() {
            this.form.user_id = this.auth.user.id;
            this.form.post(this.route('tickets.store'))
        },
    },
}
</script>
