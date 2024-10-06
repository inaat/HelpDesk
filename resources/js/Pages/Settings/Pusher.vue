<template>
    <div>
        <Head :title="title" />
        <div class="bg-white rounded-md shadow overflow-hidden mr-2">
            <form @submit.prevent="update">
                <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
                    <text-input type="text" v-model="form.PUSHER_APP_ID" :error="form.errors.PUSHER_APP_ID" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Pusher App ID')" />
                    <text-input type="text" v-model="form.PUSHER_APP_KEY" :error="form.errors.PUSHER_APP_KEY" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Pusher App Key')" />
                    <text-input type="text" v-model="form.PUSHER_APP_SECRET" :error="form.errors.PUSHER_APP_SECRET" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Pusher App Secret')" />
                    <text-input v-model="form.PUSHER_APP_CLUSTER" :error="form.errors.PUSHER_APP_CLUSTER" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Pusher App Cluster')" />
                </div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
                    <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Save') }}</loading-button>
                </div>
            </form>

        </div>
    </div>
</template>

<script>
import { Link, Head } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import Layout from '@/Shared/Layout'
import Pagination from '@/Shared/Pagination'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'
import axios from 'axios'

export default {
    metaInfo: { title: 'Priorities' },
    components: {
        Icon,
        Link,
        Head,
        Pagination,
        TextInput,
        SelectInput,
        LoadingButton,
    },
    layout: Layout,
    props: {
        title: String,
        keys: Object,
    },
    remember: 'form',
    data() {
        return {
            form: this.$inertia.form({
                PUSHER_APP_ID: this.keys['PUSHER_APP_ID']['value'],
                PUSHER_APP_KEY: this.keys['PUSHER_APP_KEY']['value'],
                PUSHER_APP_SECRET: this.keys['PUSHER_APP_SECRET']['value'],
                PUSHER_APP_CLUSTER: this.keys['PUSHER_APP_CLUSTER']['value'],
            }),
        }
    },
    methods: {
        update() {
            this.form.put(this.route('settings.pusher.update'), {
                onSuccess: () => {
                    axios.get(this.route('clear.cache','config')).then((response) => {})
                }
            })
        },
    },
}
</script>
