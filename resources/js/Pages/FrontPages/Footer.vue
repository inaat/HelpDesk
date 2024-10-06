<template>
    <div>
        <Head :title="__('Footer Area')" />
        <div class="bg-white rounded-md shadow overflow-hidden mr-2">
            <form @submit.prevent="update">
                <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
                    <textarea-input :rows="3" v-model="form.html.text" class="pr-6 pb-8 w-full" :label="__('Footer text')" />
                </div>
                <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
                    <text-input v-model="form.html.copyright" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Copyright info')" />
                </div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
                    <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Save') }}</loading-button>
                </div>
            </form>

        </div>
    </div>
</template>

<script>
import { Link, Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import LoadingButton from '@/Shared/LoadingButton'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import UploadAdapter from '@/Shared/UploadAdapter';
import TextareaInput from "@/Shared/TextareaInput";
import CKEditor from '@ckeditor/ckeditor5-vue';

export default {
    metaInfo: { title: 'Footer Area' },
    components: {
        TextareaInput,
        Link,
        Head,
        TextInput,
        LoadingButton,
        ckeditor: CKEditor.component,
    },
    layout: Layout,
    props: {
        page: Object,
    },
    remember: 'form',
    data() {
        return {
            editor: ClassicEditor,
            editorConfig: {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'insertTable', 'blockQuote', '|', 'imageUpload', 'mediaEmbed', '|', 'undo', 'redo' ],
                table: {
                    toolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                },
                extraPlugins: [this.uploader],
            },
            tabs:[
                {'name': 'Overview', 'active': true},
                {'name': 'List Information', 'active': false},
                {'name': 'Bottom', 'active': false},
            ],
            form: this.$inertia.form({
                title: 'Footer Area',
                slug: 'footer',
                is_active: this.page.is_active,
                html: JSON.parse(this.page.html),
            }),
        }
    },
    methods: {
        uploader(editor) {
            editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
                return new UploadAdapter( loader );
            };
        },
        update() {
            this.form.put(this.route('front_pages.update', 'footer'))
        },
        activeTab(index){
            for (const tab_item of this.tabs) {
                tab_item.active = false
            }
            this.tabs[index].active = true;
        },
    },
    mounted() {

    }
}
</script>
