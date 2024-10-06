<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">
      <Link class="text-indigo-400 hover:text-indigo-600" :href="this.route('cities')">{{ __('City') }}</Link>
      <span class="text-indigo-400 font-medium">/</span>
      {{ form.name }}
    </h1>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form @submit.prevent="update">
          <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
              <text-input v-model="form.name" :error="form.errors.name" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Name')" />
          </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <button class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">{{ __('Delete') }}
            {{ __('City') }}</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Update') }}
            {{ __('City') }}</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import { Link } from '@inertiajs/vue3'
import TextInput from '@/Shared/TextInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  metaInfo() {
    return { title: this.form.name }
  },
  components: {
    LoadingButton,
    TextInput,
    Link,
  },
  layout: Layout,
  props: {
    city: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
          name: this.city.name,
      }),
    }
  },
  created() {
  },
  methods: {
    update() {
      this.form.put(this.route('cities.update', this.city.id))
    },
    destroy() {
      if (confirm('Are you sure you want to delete this city?')) {
        this.$inertia.delete(this.route('cities.destroy', this.city.id))
      }
    },
  },
}
</script>
