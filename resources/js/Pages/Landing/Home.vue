<template>
    <div>
        <!-- Start Hero -->
        <Head :title="__('Home')" />
        <!-- ====== Hero Section Start -->
        <div
            id="home"
            class="relative overflow-hidden bg-primary pt-[120px] md:pt-[130px] lg:pt-[160px]"
        >
            <div class="container">
                <div class="-mx-4 flex flex-wrap items-center">
                    <div class="w-full px-4">
                        <div
                            class="hero-content wow fadeInUp mx-auto max-w-[780px] text-center"
                            data-wow-delay=".2s"
                        >
                            <h1 class="mb-8 text-3xl font-bold leading-snug text-white sm:text-4xl sm:leading-snug md:text-[45px] md:leading-snug" v-html="sanitizeHtml(html.sections[0].title)"></h1>
                            <p class="mx-auto mb-10 max-w-[600px] text-base text-[#e4e4e4] sm:text-lg sm:leading-relaxed md:text-xl md:leading-relaxed" v-html="sanitizeHtml(html.sections[0].details)"></p>
                            <ul class="mb-10 flex flex-wrap items-center justify-center gap-6">
                                <li v-for="(button, bi) in html.sections[0].buttons" :key="bi">
                                    <Link :href="button.link"
                                       class="inline-flex items-center justify-center rounded-lg bg-white py-4 px-6 text-center text-base font-medium text-dark transition duration-300 ease-in-out hover:text-primary hover:shadow-lg sm:px-10">
                                        {{ button.text }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="w-full px-4">
                        <div
                            class="wow fadeInUp relative z-10 mx-auto max-w-[845px]"
                            data-wow-delay=".25s"
                        >
                            <div class="mt-16">
                                <img v-if="html.sections[0].image" :src="html.sections[0].image" alt="" class="mx-auto max-w-full rounded-t-xl rounded-tr-xl">
                                <img v-else src="/landing/images/dashboard-helpdesk.png" alt="" class="mx-auto max-w-full rounded-t-xl rounded-tr-xl">
                            </div>
                            <div class="absolute bottom-0 -left-9 z-[-1]">
                                <img src="/images/svg/dot-1.svg" alt="dot" />
                            </div>
                            <div class="absolute -top-6 -right-6 z-[-1]">
                                <img src="/images/svg/dot-2.svg" alt="dot" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="pt-20 pb-8 lg:pt-[120px] lg:pb-[70px]">
            <div class="container">
                <div class="-mx-4 flex flex-wrap">
                    <div class="w-full px-4">
                        <div class="mb-12 max-w-[620px] lg:mb-20">
              <span class="mb-2 block text-lg font-semibold text-primary">
                {{ html.sections[1].tagline }}
              </span>
                            <h2 class="mb-4 text-3xl font-bold text-dark sm:text-4xl md:text-[42px]">
                                {{ html.sections[1].title }}
                            </h2>
                            <p class="text-lg leading-relaxed text-body-color sm:text-xl sm:leading-relaxed" v-html="html.sections[1].details"></p>
                        </div>
                    </div>
                </div>
                <div class="-mx-4 flex flex-wrap" v-if="html.sections[1]">
                    <div v-for="(feature, fi) in html.sections[1].features" :key="fi" class="w-full px-4 md:w-1/2 lg:w-1/4">
                        <div class="wow fadeInUp group mb-12 bg-white" data-wow-delay=".1s">
                            <div class="relative z-10 mb-8 flex h-[70px] w-[70px] items-center justify-center rounded-2xl bg-primary">
                                <span class="absolute top-0 left-0 z-[-1] mb-8 flex h-[70px] w-[70px] rotate-[25deg] items-center justify-center rounded-2xl bg-primary bg-opacity-20 duration-300 group-hover:rotate-45"></span>
                                <icon :name="feature.icon" class="w-8 h-8 fill-white" />
                            </div>
                            <h4 class="mb-3 text-xl font-bold text-dark">
                                {{ feature.title }}
                            </h4>
                            <p class="mb-8 text-body-color lg:mb-11" v-html="feature.details"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Start -->
        <section v-if="html.sections[2].enable_ticket_section" class="relative md:py-24 py-16 bg-gray-50 dark:bg-slate-800" id="ticketSubmit">
            <div class="container">
                <form class="card mt-2 p-4 rounded shadow-xl overflow-hidden" @submit.prevent="store" enctype="multipart/form-data">
                    <div class="px-5 ">
                        <h1 class="py-5 text-center md:text-3xl text-2xl md:leading-normal leading-normal font-semibold">{{ __('Create a ticket') }}</h1>
                        <div class="mx-auto mb-6 mt-1 w-24 border-b" />
                        <div class="flex flex-wrap">
                            <text-input v-model="form.first_name" :error="form.errors.last_name" class="pr-6 rtl:pr-0 rtl:pl-6 pb-5 md:col-span-6 lg:w-1/3" :label="__('First name')" />
                            <text-input v-model="form.last_name" :error="form.errors.last_name" class="pr-6 rtl:pr-0 rtl:pl-6 pb-5 md:col-span-6 lg:w-1/3" :label="__('Last name')" />
                            <text-input v-model="form.email" :error="form.errors.email" class=" pb-5 md:col-span-6 lg:w-1/3" :label="__('Email Address')" />
                            <text-input v-model="form.subject" :error="form.errors.subject" class=" pb-5 pr-6 rtl:pr-0 rtl:pl-6 w-full lg:w-2/3" :label="__('Subject')" />

                            <select-input v-model="form.type_id" :error="form.errors.type_id" class="pr-6 rtl:pr-0 rtl:pl-6 pb-5 md:col-span-6 lg:w-1/3" :label="__('Ticket type')">
                                <option :value="null">{{ __('Select a type') }}</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select-input>

                            <select-input @change="getCategories()" v-model="form.department_id" :error="form.errors.department_id" class="pr-6 pb-5 md:col-span-6 lg:w-1/3" :label="__('Department')">
                                <option :value="null">{{ __('Select a department') }}</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                            </select-input>

                            <select-input ref="category" v-if="form.department_id" @change="getSubCategories()" v-model="form.category_id" :error="form.errors.category_id" class=" pb-5 pr-6 md:col-span-6 lg:w-1/3" :label="__('Category')">
                                <option :value="null">{{ __('Select a category') }}</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select-input>

                            <select-input ref="sub_category"  v-if="form.category_id" v-model="form.sub_category_id" :error="form.errors.sub_category_id" class=" pb-5 md:col-span-6 lg:w-1/3" :label="__('Sub Category')">
                                <option :value="null">{{ __('Select a sub category') }}</option>
                                <option v-for="category in sub_categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select-input>

                            <div class=" pt-2 pb-8 w-full">
                                <label class="form-label" >{{ __('Request Details') }}</label>
                                <ckeditor id="ticketDetails" :editor="editor" v-model="form.details" :error="form.errors.details" :config="editorConfig"></ckeditor>
                            </div>

                        </div>

                        <!-- Attachments -->
                        <input ref="file" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf, .zip" class="hidden" multiple="multiple" @change="fileInputChange" />
                        <div class="pr-6 rtl:pl-6 pb-8 w-full lg:w-full flex-col">
                            <button type="button" class="btn flex justify-center items-center border-0" @click="fileBrowse">
                                <icon name="file" class="flex-shrink-0 h-8 fill-gray-400 pr-2 rtl:pl-2" /> <h4>{{ __('Attach files') }}</h4>
                            </button>
                            <div v-if="form.files.length" class="flex items-center justify-between pr-6 pt-8 w-full lg:w-1/2" v-for="(file, fi) in form.files" :key="fi">
                                <div class="flex-1 pr-1">
                                    {{ file.name }} <span class="text-gray-500 text-xs">({{ getFileSize(file.size) }})</span>
                                </div>
                                <button type="button" class="btn flex justify-center items-center" @click="fileRemove(file, fi)">
                                    {{ __('Remove') }}</button>
                            </div>
                        </div>
                        <!-- Attachments -->
                        <div class="flex items-start mb-5">
                            <div class="flex items-center h-5">
                                <input id="terms" type="checkbox" v-model="accept_terms" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" required>
                            </div>
                            <label for="terms" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">I agree with the <a :href="this.route('terms_service')" target="_blank" class="text-blue-600 hover:underline dark:text-blue-500">terms and conditions</a></label>
                        </div>
                    </div>
                    <div class="px-4 py-4 border-t border-gray-100 flex justify-end">
                        <loading-button :loading="form.processing" class="rounded-lg bg-primary py-3 px-6 text-base font-medium text-white duration-300 ease-in-out hover:bg-opacity-80" type="submit">Submit</loading-button>
                    </div>

                </form>
            </div><!--end container-->
        </section><!--end section-->
        <!-- End -->




    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Logo from '@/Shared/Logo'
import Layout from '@/Shared/Landing/Layout'
import Icon from '@/Shared/Icon'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import sanitizeHtml from "sanitize-html";
import UploadAdapter from '@/Shared/UploadAdapter';
import CKEditor from '@ckeditor/ckeditor5-vue';

export default {
    metaInfo: { title: 'Home' },
    layout: Layout,
    components: {
        Logo,
        Icon,
        LoadingButton,
        SelectInput,
        TextInput,
        Head,
        Link,
        ckeditor: CKEditor.component,
    },
    props: {
        all_categories: { required: true },
        departments: Array,
        types: Array,
        title: String,
        page: Object,
        auth: Object,
    },
    remember: 'form',
    data() {
        return {
            editor: ClassicEditor,
            accept_terms: false,
            html: JSON.parse(this.page.html),
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
                first_name: '',
                last_name: '',
                email: '',
                department_id: null,
                category_id: null,
                sub_category_id: null,
                type_id: null,
                subject: null,
                details: '',
                files: [],
            }),
            sanitizeHtml : sanitizeHtml
        }
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
        uploadFiles() {
            this.form.files = this.$refs.files.files
        },
        store() {
            if(!this.accept_terms){
                alert('You must need to accept the terms and condition.')
            }
            this.form.post(this.route('ticket_store'),{
                onSuccess: () => {
                    this.form.reset()
                },
                // form.reset()
            })
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
    },
    created() {
        if(this.auth.user){
            this.form.first_name = this.auth.user.first_name;
            this.form.last_name = this.auth.user.last_name;
            this.form.email = this.auth.user.email;
        }
    }
}
</script>




