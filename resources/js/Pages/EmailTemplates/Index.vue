<template>
  <div>
    <Head :title="__(title)" />
    <div class="mb-6 flex justify-between items-center">
      <search-input v-model="form.search" class="w-full max-w-md mr-4" @reset="reset"></search-input>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="px-6 pt-6 pb-4">{{ __('Name') }}</th>
          <th class="px-6 pt-6 pb-4">{{ __('Slug') }}</th>
          <th class="px-6 pt-6 pb-4">{{ __('Details') }}</th>
        </tr>
        <tr v-for="template in templates.data" :key="template.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <Link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="this.route('templates.edit', template.id)">
              {{ template.name }}
            </Link>
          </td>
            <td class="border-t">
                <Link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="this.route('templates.edit', template.id)">
                    {{ template.slug }}
                </Link>
            </td>
            <td class="border-t">
                <Link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="this.route('templates.edit', template.id)">
                    {{ template.details }}
                </Link>
            </td>
          <td class="border-t w-px">
            <Link class="px-4 flex items-center" :href="this.route('templates.edit', template.id)" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="templates.data.length === 0">
          <td class="border-t px-6 py-4" colspan="4">No templates found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="templates.links" />
  </div>
</template>

<script>
import { Link, Head } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination'
import SearchInput from '@/Shared/SearchInput'

export default {
  metaInfo: { title: 'Email Templates' },
  components: {
    Icon,
    Link,
    Head,
    Pagination,
      SearchInput,
  },
  layout: Layout,
  props: {
    title: String,
    filters: Object,
      templates: Object,
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
      handler: throttle(function() {
        this.$inertia.get(this.route('templates'), pickBy(this.form), { preserveState: true })
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
