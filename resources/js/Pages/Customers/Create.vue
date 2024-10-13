<template>
  <div>
    <Head :title="__(title)" />
    <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
        
          <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Contact name')" />
          <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Remark')" />
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Email')" />
          <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/2" :label="__('Phone')" />
          <div class="pr-6 pb-8 w-full lg:w-1/3"><label class="form-label" for="select-input-b0330f95-1535-4b81-bc08-e79cb06f9121">{{ __('Customers')}}</label>
  <select v-model="form.organization_id"  @change="updateOrganization" class="form-select" :class="{ error: form.errors.organization_id }">
    <option :value="null">{{ __('Select an Customers') }}</option>
    <option v-for="organization in organizations" :key="organization.id" :value="organization.id">
        {{ __(organization.name) }}
    </option>
</select>
    <div v-if="form.errors.organization_id" class="form-error">
      {{ form.errors.organization_id }}
    </div>

            </div>
          <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/2" :label="__('City')" />
          <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Address')" />
          <div class="pr-6 pb-8 w-full lg:w-1/3"><label class="form-label" for="select-input-b0330f95-1535-4b81-bc08-e79cb06f9121">{{ __('Country')}}</label>
            <select v-model="form.country_id" class="form-select" >
    <option :value="null">{{ __('Select a Country') }}</option>
    <option v-for="country in countries" :key="country.id" :value="country.id">
        {{ __(country.name) }}
    </option>
  </select>
    <div v-if="form.errors.country_id" class="form-error">
      {{ form.errors.country_id }}
    </div>

            </div>

          <text-input v-model="form.password" :error="form.errors.password" class="pb-8 pr-6 w-full lg:w-1/3" type="password" autocomplete="new-password" :label="__('Password')" />
          <file-input v-model="form.photo" :error="form.errors.photo" class="pb-8 pr-6 w-full lg:w-1/2" type="file" accept="image/*" :label="__('Photo')" />
        </div>
        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">{{ __('create') }}</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import FileInput from '@/Shared/FileInput'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'
import axios from 'axios'

export default {
  components: {
    FileInput,
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
  },
  layout: Layout,
  props: {
    organizations: Array,
    countries: Array,
    cities: Array,
    title: String,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        city: null,
        address: '',
        country_id: null,
        password: '',
        role_id: null,
        photo: null,
        organization_id: null,
        customer_no:null,
      }),
    }
  },
  created() {
    this.setDefaultValue(this.countries, 'country_id', 'Saudi Arabia')
  },
  methods: {
    updateOrganization() {
  if (this.form.organization_id) {
    axios.get(this.route('get.organization'), {
      params: {
        organization_id: this.form.organization_id
      }
    })
    .then(response => {
      console.log("Fetched Organization:", response.data);
      this.form.city = response.data.city;
      this.form.address = response.data.address;
      
      // Reset country_id to trigger reactivity
      this.form.country_id = null; 

      this.$nextTick(() => {
        // Now set the country_id
        this.form.country_id = response.data.country;

        if (response.data.country) {
          console.log(`Country ID set to: ${this.form.country_id}`);
        } else {
          console.warn("No country_id received from the API");
          this.form.country_id = null;
        }
      });
    })
    .catch(error => {
      console.error("An error occurred while fetching data:", error);
    });
  } else {
    // Reset fields if no organization is selected
    this.form.city = null;
    this.form.country_id = null;
  }
},
    setDefaultValue(arr, key, value){
      const find = arr.find(i=>i.name.match(new RegExp(value + ".*")))
      if(find){
        this.form[key] = find['id']
      }
    },
    store() {
      
      this.form.post(this.route('customers.store'))
    },
  },
}
</script>
