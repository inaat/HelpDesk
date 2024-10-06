<template>
  <div>
      <Head :title="__(title)" />
    <div class="mb-6 flex justify-between items-center">
      <search-input v-model="form.search" class="w-full max-w-md mr-4" @reset="reset"></search-input>
      <Link class="btn-indigo" :href="route('posts.create')">
        <span>{{ __('Create a new post') }}</span>
      </Link>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="px-6 pt-6 pb-4">{{ __('Title') }}</th>
          <th class="px-6 pt-6 pb-4">{{ __('Type') }} </th>
          <th class="px-6 pt-6 pb-4">{{ __('Is Active') }}</th>
          <th class="px-6 pt-6 pb-4">Created At</th>
        </tr>
        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <Link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="route('posts.edit', post.id)">
              {{ post.title }}
            </Link>
          </td>
          <td class="border-t">
            <Link class="px-6 py-4 flex items-center" :href="route('posts.edit', post.id)" tabindex="-1">
              {{ post.type }}
            </Link>
          </td>
            <td class="border-t">
                <Link class="px-6 py-4 flex items-center" :href="route('posts.edit', post.id)" tabindex="-1">
                    {{ post.isActive }}
                </Link>
            </td>
            <td class="border-t">
                <Link class="px-6 py-4 flex items-center" :href="route('posts.edit', post.id)" tabindex="-1">
                    {{ post.created_at }}
                </Link>
            </td>
          <td class="border-t w-px">
            <Link class="px-4 flex items-center" :href="route('posts.edit', post.id)" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="posts.data.length === 0">
          <td class="border-t px-6 py-4" colspan="4">{{ __('No post base found.') }}</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="posts.links" />
  </div>
</template>

<script>
import Icon from '@/Shared/Icon'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination'
import SearchInput from '@/Shared/SearchInput'
import { Link, Head } from '@inertiajs/vue3'

export default {
  metaInfo: { title: 'Blog posts' },
  components: {
    Icon,
    Pagination,
      SearchInput,
    Link,
      Head,
  },
  layout: Layout,
  props: {
      filters: Object,
      posts: Object,
      title: String,
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
        this.$inertia.get(this.route('posts'), pickBy(this.form), { preserveState: true })
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
