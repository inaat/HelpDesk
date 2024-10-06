<template>
    <div>
        <Head :title="__(title)" />
        <div class="main flex-1 flex flex-col sec_chat">

            <div class="flex-1 flex h-full gap-5">
                <div class="sidebar h-full hidden lg:flex w-1/3 flex-2 flex-col bg-white">
                    <div class="chat-box h-full">
                        <div class="chat-left-aside h-full">
                            <div class="c-top">
                                <div class="media mb-1">
                                    <img v-if="user.photo" :src="user.photo" alt="" class="rounded-circle rounded-full user-image">
                                    <img v-else src="/images/svg/profile.svg" alt="" class="rounded-circle rounded-full user-image">
                                    <div class="about">
                                        <div class="name f-w-600">{{ user.first_name }} {{ user.last_name }}</div>
                                        <div class="status">{{ __(user.role.name) }}</div>
                                    </div>
                                </div>
                                <div class="search flex-2 py-2">
                                    <search-input v-model="form.search" class="w-full" @reset="reset"></search-input>
                                </div>
<!--                                <div class="search flex-2 py-2">-->
<!--                                    <input type="text" class="outline-none p-2 mt-3 block w-full bg-transparent border rounded border-gray-200" placeholder="Search">-->
<!--                                </div>-->
                            </div>
                            <div class="c-list">
                                <div class="flex-1 h-full overflow-hidden overflow-y-auto pt-2">
                                    <Link v-for="conversation in conversations.data" :key="conversation.id" :href="route('chat.current', conversation.slug || conversation.id)"
                                         class="entry cursor-pointer transform hover:scale-105 duration-300 transition-transform mb-4 rounded p-4 mx-5 flex shadow-md"
                                         v-bind:class="{ 'border-l-4 border-red-500': chat && (conversation.id === chat.id) }">
                                        <div class="flex-2">
                                            <div class="w-12 h-12 relative">
                                                <span class="w-12 h-12 rounded-full mx-auto user_icon" alt="chat-user">{{ Array.from(conversation.creator.first_name)[0] }}</span>
<!--                                                <span class="absolute w-4 h-4 bg-gray-400 rounded-full right-0 bottom-0 border-2 border-white"></span>-->
                                            </div>
                                        </div>
                                        <div class="flex-1 px-2">
                                            <div class="truncate w-32"><span class="">{{ conversation.creator.first_name }} {{ conversation.creator.last_name }}</span></div>
                                            <div class="mt-1" v-if="conversation.creator.email"><small class="text-gray-600">{{ conversation.creator.email }}</small></div>
                                            <div class="mt-1 truncate w-32" v-if="conversation.title"><small class="text-gray-600 text-xs">{{ conversation.title }}</small></div>
                                        </div>
                                        <div class="flex-2 text-right">
                                            <div><small class="text-gray-500">{{ moment(conversation.updated_at).format('DD MMM') }}</small></div>
                                            <div>
                                                <small v-if="conversation.total_entry" class="text-xs bg-red-500 text-white rounded-full h-6 w-6 leading-6 text-center inline-block">
                                                    {{ conversation.total_entry }}
                                                </small>
                                            </div>
                                        </div>
                                    </Link>
                                    <div v-if="!conversations.data.length" class="flex w-full h-full justify-center items-center text-gray-500">{{ __('No conversation found.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-area h-full w-2/3 flex-1 flex flex-col bg-white">
                    <div class="flex-3">
                        <div class="chat-header flex pb-3 justify-between" v-if="chat">
                            <div class="flex">
                                <div class="w-12 h-12 relative mr-3">
                                    <span class="w-12 h-12 rounded-full mx-auto user_icon" title="{{ chat.creator.first_name }} {{ chat.creator.last_name }}">{{ Array.from(chat.creator.first_name)[0] }}</span>
                                </div>
                                <div class="flex flex-col pt-1">
                                    <div class="name"> {{ chat.creator.first_name }} {{ chat.creator.last_name }}</div>
                                    <div class="status digits pt-2"> {{ chat.creator.email }} </div>
                                </div>
                            </div>
                            <div v-if="user_access.chat.delete" class="flex justify-center mr-3 items-center cursor-pointer" @click="destroy(chat.id)"><icon name="trash" class="w-5 h-5" /></div>
                        </div>
                    </div>
                    <div class="messages flex-1 overflow-auto reverse__order">
                        <div v-if="chat && chat.messages.length" v-for="(message, index) in chat.messages.slice().reverse()" :key="index" class="message mb-4 flex">
                            <div v-if="!message.user_id && message.contact && message.contact.first_name" class="flex-2">
                                <div class="w-12 h-12 relative">
                                    <span class="w-12 h-12 rounded-full mx-auto user_icon">{{ Array.from(message.contact.first_name)[0] }}</span>
                                </div>
                            </div>
                            <div v-if="!message.user_id" class="flex-1 px-2 mt__wrap">
                                <div class="inline-block bg-gray-300 rounded p-2 px-3 text-gray-700 leading-5">
                                    <span>{{ message.message }}</span>
                                </div>
                                <div class="pl-4"><small class="text-gray-500">{{ moment(message.updated_at).fromNow(true) }}</small></div>
                            </div>

                            <div v-if="message.user_id" class="flex-1 px-2 text-right mt__wrap">
                                <div class="inline-block bg-blue-600 rounded p-2 px-3 text-white leading-5">
                                    <span>{{ message.message }}</span>
                                </div>
                                <div class="pr-4"><small class="text-gray-500">{{ moment(message.updated_at).fromNow(true) }}</small></div>
                            </div>
                        </div>
                        <div v-else-if="chat && !chat.messages.length" class="flex w-full h-full justify-center items-center text-gray-500">{{ __('No history found for this conversation') }}</div>
                        <div v-else class="flex w-full h-full justify-center items-center text-gray-500">{{ __('Click on a conversation from left to see the histories.') }}</div>
                    </div>
                    <div class="flex-2 pt-4 pb-3" v-if="chat">
                        <div class="write bg-white shadow flex rounded-lg">
<!--                            <div class="flex-3 flex content-center items-center text-center p-4 pr-0">-->
<!--                                            <span class="block text-center text-gray-400 hover:text-gray-800">-->
<!--                                                <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6"><path d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>-->
<!--                                            </span>-->
<!--                            </div>-->
                            <div class="flex-1">
                                <textarea v-model="message" name="message" @keydown.enter.exact.prevent="sendMessage" class="w-full block outline-none text-sm py-4 px-4 bg-transparent" rows="1" :placeholder="__('Type a message...')" autofocus></textarea>
                            </div>
                            <div class="flex-2 w-35 p-2 flex content-center items-center">
<!--                                <div class="flex-1 text-center">-->
<!--                                                <span class="text-gray-400 hover:text-gray-800">-->
<!--                                                    <span class="inline-block align-text-bottom">-->
<!--                                                        <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>-->
<!--                                                    </span>-->
<!--                                                </span>-->
<!--                                </div>-->
                                <div class="flex-1">
                                    <button class="bg-blue-400 w-10 h-10 rounded-full flex justify-center items-center" @click="sendMessage">
                                        <icon class="w-4 h-4 fill-gray-100" name="send"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
import moment from 'moment'
import axios from 'axios'

export default {
    metaInfo: { title: 'Chat' },
    components: {
        SearchInput,
        Icon, Pagination, Link, Head
    },
    layout: Layout,
    props: {
        filters: Object,
        conversations: Object,
        chat: Object,
        title: String
    },
    remember: 'form',
    data() {
        return {
            message: null,
            user: this.$page.props.auth.user,
            user_access: this.$page.props.auth.user.access,
            form: {
                search: this.filters.search,
            },
        }
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function() {
                this.$inertia.get(this.route('chat'), pickBy(this.form), { preserveState: true })
            }, 150),
        },
    },
    methods: {
        reset() {
            this.form = mapValues(this.form, () => null)
        },
        sendMessage(){
            var that = this;
            const messageData = {
                message: this.message,
                user_id: this.user.id,
                _token: this.$page.props.csrf_token,
                conversation_id: this.chat.id
            }
            this.message = ''
            axios.post(this.route('chat.message'), messageData).then((response) => {
                if(response.data){
                    that.chat.messages.push(response.data)
                }
                this.$emit('message sent!')
            }).catch((error) => {
                console.log(error)
            })
        },
        navigateTo(id){
            window.location.href = this.route('chat.current', id);
        },
        destroy(id) {
            let that = this
            if (confirm('Are you sure you want to delete this conversation?')) {
                this.$inertia.delete(this.route('chat.destroy', id),{
                    onSuccess: () => {
                        setTimeout(function (){ that.reset() }, 4000)
                    }
                })
            }
        },
        restore(id) {
            if (confirm('Are you sure you want to restore this conversation?')) {
                this.$inertia.put(this.route('chat.restore', id))
            }
        },
        pushMessage(message){
            let chat = this.conversations.data.find(x => x.id === message.conversation_id)
            if(typeof chat === 'object' ){
                chat.title = message.message
                chat.total_entry = chat.total_entry + 1

                if(!!this.chat && (this.chat.id === message.conversation_id)){
                    this.chat.messages.push(message)
                    chat.total_entry = 0
                }
            }else{
                this.conversations.data.unshift({'id': message.conversation_id, 'total_entry': 1, 'title': message.message, 'creator': message.creator, 'updated_at': message.updated_at});
            }
        }
    },
    created(){
        this.moment = moment
        let that = this
        if(window.Echo){
            window.Echo.private('messages').listen('NewChatMessage', function (e){
                if(typeof e.chatMessage === 'object'){
                    that.pushMessage(e.chatMessage)
                }
            })
        }
    },
}


</script>
