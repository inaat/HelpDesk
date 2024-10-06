<template>
  <div>
      <Head :title="title" />
    <div class="bg-white rounded-md shadow overflow-hidden max-w-full">
      <form @submit.prevent="update">
          <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
              <text-input v-model="form.title" :error="form.errors.title" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Title')" />
              <select-input v-model="form.type_id" :error="form.errors.type_id" class="pr-6 pb-8 w-full lg:w-1/4" :label="__('Type')" :required="true">
                  <option :value="null">{{ __('Select type') }}</option>
                  <option v-for="s in types" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select-input>
              <select-input v-model="form.is_active" :error="form.errors.is_active" class="pr-6 pb-8 w-full lg:w-1/4" :label="__('Is Active')" :required="true">
                  <option :value="1">Yes</option>
                  <option :value="0">No</option>
              </select-input>

              <div class="pr-6 pb-8 w-full">
                  <label class="form-label" >Details:</label>
                  <ckeditor id="ticketDetails" :editor="editor" v-model="form.details" :error="form.errors.details" :config="editorConfig"></ckeditor>
              </div>
              <file-input v-model="form.image" :error="form.errors.image" class="pb-8 pr-6 w-full lg:w-1/3" type="file" accept="image/*" :label="__('Feature image')" />
              <div class="w-full flex lg:w-1/3 justify-start"><img v-if="post.image" class="block mb-2 w-10 h-10" :src="post.image" /></div>
          </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <button class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete Post</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">Update Post</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import TextareaInput from '@/Shared/TextareaInput'
import LoadingButton from '@/Shared/LoadingButton'
import { Link, Head } from '@inertiajs/vue3'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import FileInput from '@/Shared/FileInput'
import UploadAdapter from '@/Shared/UploadAdapter';
import CKEditor from '@ckeditor/ckeditor5-vue';

export default {
  components: {
    LoadingButton,
      SelectInput,
      TextareaInput,
      TextInput,
    Link,
      Head,
      FileInput,
      ckeditor: CKEditor.component,
  },
  layout: Layout,
  props: {
      post: Object,
      title: String,
      types: Array,
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
      form: this.$inertia.form({
          _method: 'put',
          title: this.post.title,
          type_id: this.post.type_id,
          is_active: this.post.is_active,
          image: null,
          details: this.post.details,
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
        this.form.post(this.route('posts.update', this.post.id), {
            onSuccess: () => this.form.reset('image'),
        })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this post?')) {
        this.$inertia.delete(this.route('posts.destroy', this.post.id))
      }
    },
  },
}
</script>
