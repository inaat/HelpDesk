<template>
  <div>
    <Head :title="title" />
    <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="update">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
        
          <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Contact name')" />
          <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Remark')" />
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Email')" />
          <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Phone')" />
          <!-- <select-input
      v-model="form.organization_id"
      :error="form.errors.organization_id"
      class="pb-8 pr-6 w-full lg:w-1/2"
      :label="__('Customers')"
      @input="updateOrganization"
    >
      <option :value="null">{{ __('Select an Customers') }}</option>
      <option
        v-for="organization in organizations"
        :key="organization.id"
        :value="organization.id"
      >
        {{ organization.name }}
      </option>
    </select-input> -->
    
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
    
            <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('City')" />
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
          <file-input v-model="form.photo_path" :error="form.errors.photo_path" class="pb-8 pr-6 w-full lg:w-1/3" type="file" accept="image/*" label="Photo" />
            <div class="w-full lg:w-1/3 flex items-center justify-start"><img v-if="user.photo_path" class="block mb-2 w-8 h-8 rounded-full" :src="user.photo_path" /></div>
        </div>
        <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
          <button v-if="user.id !== auth.user.id && user_access.customer.delete" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
            {{ __('Delete') }}</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Update') }}</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import FileInput from '@/Shared/FileInput'
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
    user: Object,
    auth: Object,
    countries: Array,
    cities: Array,
    title: String,
    organizations: Array,
  },
  remember: 'form',
  data() {
    return {
        user_access: this.$page.props.auth.user.access,
      form: this.$inertia.form({
        _method: 'put',
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        city: this.user.city,
        address: this.user.address,
        country_id: this.user.country_id,
        organization_id:this.user.organization_id,
        customer_no:this.user.customer_no,
        password: '',
          photo_path: null
      }),
    }
  },
  created() {
    //this.setDefaultValue(this.countries, 'country_id', 'United States')
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
    update() {
      this.form.post(this.route('customers.update', this.user.id), {
        onSuccess: () => this.form.reset('password', 'photo'),
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this user?')) {
        this.$inertia.delete(this.route('customers.destroy', this.user.id))
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this user?')) {
        this.$inertia.put(this.route('customers.restore', this.user.id))
      }
    },
  },
  watch: {
    'form.country_id': function(newVal, oldVal) {
      console.log(`country_id changed from ${oldVal} to ${newVal}`);
    }
  }
}
</script>
