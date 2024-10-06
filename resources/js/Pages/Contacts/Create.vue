<template>
  <div>
    <Head :title="__(title)" />
    <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
          <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('First name')" />
          <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Last name')" />
          <text-input v-model="form.title" :error="form.errors.title" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Title')" />
          <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Phone')" />
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Email')" />
          <select-input v-model="form.organization_id" :error="form.errors.organization_id" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Organization')">
            <option :value="null" />
            <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.name }}</option>
          </select-input>
          <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Address')" />
          <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('City')" />
          <select-input v-model="form.country" :error="form.errors.country" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Country')">
              <option :value="null" />
              <option v-for="c in countries" :key="c.id" :value="c.code">{{ __(c.name) }}</option>
          </select-input>
          <select-input v-model="form.department_id" :error="form.errors.department_id" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Department')">
            <option :value="null" />
            <option v-for="s in departments" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select-input>
          <select-input v-model="form.status" :error="form.errors.status" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Status')">
            <option value="1">Active</option>
            <option value="0">InActive</option>
          </select-input>
        </div>
        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">{{ __('Create') }} {{ __('Contact') }}</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  components: {
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
  },
  layout: Layout,
  props: {
    organizations: Array,
    departments: Array,
      countries: Array,
      title: String,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        first_name: '',
        last_name: '',
        title: '',
        organization_id: null,
        department_id: null,
        email: '',
        phone: '',
        address: '',
        city: '',
        country: '',
        status: 1,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('contacts.store'))
    },
  },
}
</script>
