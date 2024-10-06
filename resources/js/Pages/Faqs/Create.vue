<template>
  <div>
    <Head :title="__(title)" />
    <div class="bg-white rounded-md shadow overflow-hidden max-w-full">
      <form @submit.prevent="store">
        <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
          <text-input v-model="form.name" :error="form.errors.name" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Name')" />

            <select-input v-model="form.status" :error="form.errors.status" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Status')">
                <option :value="1">{{ __('Active') }}</option>
                <option :value="0">{{ __('Inactive') }}</option>
            </select-input>

            <textarea-input id="ticketDetails" name="content" v-model="form.details" :error="form.errors.details" class="pr-6 pb-8 w-full" :placeholder="__('Faq Description')"></textarea-input>
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end items-center">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">{{ __('Create FAQ') }}</loading-button>
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

export default {
  metaInfo: { title: 'Create FAQ' },
  components: {
      LoadingButton,
      SelectInput,
      TextareaInput,
      TextInput,
      Link,
      Head,
  },
  layout: Layout,
  props: {
      title: String
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: null,
        status: 1,
        details: null
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('faqs.store'))
    },
  },
}
</script>
