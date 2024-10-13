<template>
  <div>
    <Head :title="__(title)" />
    <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="flex items-center flex-wrap -mb-8 -mr-6 p-8">
              <!-- Customer Details -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.customer_no" :error="form.errors.customer_no" type="number"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Customer No')" />
                <text-input v-model="form.name_en" :error="form.errors.name_en" class="pb-8 pr-6 w-full lg:w-2/3"
                  :label="__('Name En')" />
               
              </div>
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                
                <text-input v-model="form.name" :error="form.errors.name" class="pb-8 pr-6 w-full lg:w-3/3"
                  :label="__('Name Ar')" />
               
              </div>
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.email" :error="form.errors.email" type="email"
                  class="pb-8 pr-6 w-full lg:w-2/3" :label="__('Email')" />
                  <text-input v-model="form.phone" :error="form.errors.phone" type="tel" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Phone')" />
              </div>

              <!-- Address Details -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
               
                <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-2/3"
                  :label="__('Address')" />
                <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('City')" />
              </div>

              <!-- Additional Fields -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.region" :error="form.errors.region" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Province/State')" />
                <select-input v-model="form.country" :error="form.errors.country" class="pb-8 pr-6 w-full lg:w-2/3"
                  :label="__('Country')">
                  <option :value="null" />
                  <option v-for="c in countries" :key="c.id" :value="c.id">{{ __(c.name) }}</option>
                </select-input>
             
              </div>
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.postal_code" :error="form.errors.postal_code"
                class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Postal Code')" />
                <text-input v-model="form.web_site" :error="form.errors.web_site" type="text"
                class="pb-8 pr-6 w-full lg:w-2/3" :label="__('Website')" />
              </div>

              <!-- Contact Persons -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.contact_person_1" :error="form.errors.contact_person_1"
                  class="pb-8 pr-6 w-full lg:w-3/3" :label="__('Contact Person 1')" />
                <text-input v-model="form.contact_person_2" :error="form.errors.contact_person_2"
                  class="pb-8 pr-6 w-full lg:w-3/3" :label="__('Contact Person 2')" />
                 
              </div>

              <!-- Additional Contact Information -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
               
              </div>
            </div>
        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">{{ __('Create Customer ') }}</loading-button>
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
    countries: Array,
    title: String,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: null,
        name_en: null,
        email: null,
        phone: null,
        address: null,
        city: null,
        region: null,
        country: null,
        postal_code: null,
        customer_no: null,
        contact_person_1: null, // New field
        contact_person_2: null, // New field
        add_1: null, // New field
        add_2: null, // New field
        add_3: null, // New field
        add_4: null, // New field
        phone_1: null, // New field
        phone_2: null, // New field
        phone_3: null, // New field
        fax_1: null, // New field
        fax_2: null, // New field
        mobile_1: null, // New field
        mobile_2: null, // New field
        web_site: null, // New field
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('organizations.store'))
    },
  },
}
</script>
