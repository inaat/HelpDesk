<template>
  <div>

    <Head :title="title" />
    <div class="flex flex-wrap">
      <div class="lg:w-2/5">
        <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
          <form @submit.prevent="update">
            <div class="flex flex-wrap -mb-8 -mr-6 p-8">
              <!-- Customer Details -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.customer_no" :error="form.errors.customer_no" type="number"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Customer No')" />
                <text-input v-model="form.name" :error="form.errors.name" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Name')" />
                <text-input v-model="form.email" :error="form.errors.email" type="email"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Email')" />
              </div>

              <!-- Address Details -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.phone" :error="form.errors.phone" type="tel" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Phone')" />
                <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Address')" />
                <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('City')" />
              </div>

              <!-- Additional Fields -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.region" :error="form.errors.region" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Province/State')" />
                <select-input v-model="form.country" :error="form.errors.country" class="pb-8 pr-6 w-full lg:w-1/3"
                  :label="__('Country')">
                  <option :value="null" />
                  <option v-for="c in countries" :key="c.id" :value="c.id">{{ __(c.name) }}</option>
                </select-input>
                <text-input v-model="form.postal_code" :error="form.errors.postal_code"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Postal Code')" />
              </div>

              <!-- Contact Persons -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.contact_person_1" :error="form.errors.contact_person_1"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Contact Person 1')" />
                <text-input v-model="form.contact_person_2" :error="form.errors.contact_person_2"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Contact Person 2')" />
              </div>

              <!-- Additional Contact Information -->
              <div class="flex flex-wrap -mb-8 -mr-6 w-full">
                <text-input v-model="form.web_site" :error="form.errors.web_site" type="url"
                  class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Website')" />
              </div>
            </div>

            <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
              <button class="text-red-600 hover:underline" type="button" @click="destroy">
                {{ __('Delete Organization') }}
              </button>
              <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">
                {{ __('Update Organization') }}
              </loading-button>
            </div>
          </form>
        </div>
      </div>
      <div class="max-w-full lg:w-3/5 " >
      
        <div class="bg-white rounded-md shadow overflow-hidden ml-2 chat-area comment-box flex-1 flex flex-col">
          <h2 class=" text-2xl font-bold">{{ __('Contacts') }}</h2>
          <table class="w-full whitespace-nowrap">
            <tr class="text-left font-bold">
              <th class="pb-4 pt-6 px-6">{{ __('Name') }}</th>
              <th class="pb-4 pt-6 px-6">{{ __('City') }}</th>
              <th class="pb-4 pt-6" colspan="2">{{ __('Phone') }}</th>
            </tr>
            <tr v-for="contact in organization.contacts" :key="contact.id"
              class="hover:bg-gray-100 focus-within:bg-gray-100">
              <td class="border-t">
                <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                  :href="route('customers.edit', contact.id)">
                {{ contact.name }}
                </Link>
              </td>
              <td class="border-t">
                <Link class="flex items-center px-6 py-4" :href="route('customers.edit', contact.id)" tabindex="-1">
                {{ contact.city }}
                </Link>
              </td>
              <td class="border-t">
                <Link class="flex items-center px-6 py-4" :href="route('customers.edit', contact.id)" tabindex="-1">
                {{ contact.phone }}
                </Link>
              </td>
              <td class="w-px border-t">
                <Link class="flex items-center px-4" :href="route('customers.edit', contact.id)" tabindex="-1">
                <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
                </Link>
              </td>
            </tr>
            <tr v-if="organization.contacts.length === 0">
              <td class="px-6 py-4 border-t" colspan="4">No contacts found.</td>
            </tr>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>
<script>
import { Head, Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  components: {
    Head,
    Icon,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
  },
  layout: Layout,
  props: {
    organization: Object,
    countries: Array,
    title: String,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: this.organization.name,
        email: this.organization.email,
        phone: this.organization.phone,
        address: this.organization.address,
        city: this.organization.city,
        region: this.organization.region,
        country: this.organization.country,
        postal_code: this.organization.postal_code,
        customer_no: this.organization.customer_no,
        contact_person_1: this.organization.contact_person_1, // New field
        contact_person_2: this.organization.contact_person_2, // New field
        add_1: this.organization.add_1, // New field
        add_2: this.organization.add_2, // New field
        add_3: this.organization.add_3, // New field
        add_4: this.organization.add_4, // New field
        phone_1: this.organization.phone_1, // New field
        phone_2: this.organization.phone_2, // New field
        phone_3: this.organization.phone_3, // New field
        fax_1: this.organization.fax_1, // New field
        fax_2: this.organization.fax_2, // New field
        mobile_1: this.organization.mobile_1, // New field
        mobile_2: this.organization.mobile_2, // New field
        web_site: this.organization.web_site, // New field
      }),
    }
  },
  methods: {
    update() {
      this.form.put(route('organizations.update', this.organization.id))
    },
    destroy() {
      if (confirm('Are you sure you want to delete this organization?')) {
        this.$inertia.delete(route('organizations.destroy', this.organization.id))
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this organization?')) {
        this.$inertia.put(route('organizations.restore', this.organization.id))
      }
    },
  },
}
</script>
