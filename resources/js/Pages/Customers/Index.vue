<template>
  <div>
    <Head :title="__('Customers')" />
    <div class="flex items-center justify-between mb-6">
      <search-input v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset"></search-input>
      <Link class="btn-indigo" :href="route('customers.create')">
        <span>{{ __('Create New') }}</span>
      </Link>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="pb-4 pt-6 px-6">{{ __('Name') }}</th>
          <th class="pb-4 pt-6 px-6">{{ __('Email') }}</th>
          <th class="pb-4 pt-6 px-6">{{ __('Phone') }}</th>
            <th class="pb-4 pt-6 px-6">{{ __('City') }}</th>
            <th class="pb-4 pt-6 px-6">{{ __('Created') }}</th>
        </tr>
        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <Link class="flex items-center px-6 py-4 focus:text-indigo-500" :href="route('customers.edit',user.id)">
              <img v-if="user.photo" class="block -my-2 mr-2 w-5 h-5 rounded-full" :src="user.photo" />
              {{ user.name }}
            </Link>
          </td>
          <td class="border-t">
            <Link class="flex items-center px-6 py-4" :href="route('customers.edit',user.id)" tabindex="-1">
              {{ user.email }}
            </Link>
          </td>
          <td class="border-t">
            <Link class="flex items-center px-6 py-4" :href="route('customers.edit',user.id)" tabindex="-1">
              {{ user.phone }}
            </Link>
          </td>
            <td class="border-t">
                <Link class="flex items-center px-6 py-4" :href="route('customers.edit',user.id)" tabindex="-1">
                    {{ __(user.city) }}
                </Link>
            </td>
            <td class="border-t">
                <Link class="flex items-center px-6 py-4" :href="route('customers.edit',user.id)" tabindex="-1">
                    {{ __(user.created_at) }}
                </Link>
            </td>
          <td class="w-px border-t">
            <Link class="flex items-center px-4" :href="route('customers.edit',user.id)" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="users.data.length === 0">
          <td class="px-6 py-4 border-t" colspan="5">{{__('No customers found.')}}</td>
        </tr>
      </table>
    </div>
      <pagination class="mt-6" :links="users.links" />
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination'
import SearchInput from '@/Shared/SearchInput'

export default {
  components: {
      SearchInput,
    Head,
    Icon,
    Link,
      Pagination,
  },
  layout: Layout,
  props: {
    filters: Object,
    users: Object,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('customers'), pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>
