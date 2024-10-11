<template>
    <div>

        <Head :title="__(title)" />
        <div class="flex flex-col md:flex-row gap-3 mb-4 justify-between items-center ticket-filters">
            <search-input v-model="form.search" placeholder="Search by Key, Subject, Priority, Status, Assign to..."
                class="w-full max-w-md search" @reset="reset"></search-input>
            <div class="filter-add-new flex flex-col gap-3 md:flex-row items-center">
                <a v-if="auth.user.role.slug === 'admin'"
                    class="uppercase gap-[1px] text-sm px-3 py-2 flex items-center justify-center"
                    href="/dashboard/ticket/csv/export">
                    <img class="w-6 h-6" src="/images/svg/export-csv.svg" alt="Export CSV" />
                    <span>{{ __('Export') }}</span>
                </a>
                <label v-if="auth.user.role.slug === 'admin'" for="importCSV"
                    class="uppercase gap-[1px] text-sm px-3 py-2 flex items-center justify-center">
                    <img class="w-6 h-6" src="/images/svg/import-csv.svg" alt="Import CSV" />
                    <span>{{ __('Import') }}</span>
                    <input @change="uploadImportCSV" class="hidden" id="importCSV" type="file" />
                </label>
                <select v-model="form.limit"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <Link class="btn-indigo" :href="this.route('tickets.create')">
                <span>{{ __('New Ticket') }}</span>
                </Link>
            </div>
        </div>
        <div class="flex flex-col gap-3 mb-4 md:flex-row w-full items-center ticket-filters">
            <div class="mr-2 w-full">{{ __('Filter By') }}:</div>
            <select-input v-if="!(hidden_fields && hidden_fields.includes('ticket_type'))" v-model="form.type_id"
                class="mr-2 w-full">
                <option :value="null">{{ __('Type') }}</option>
                <option v-for="s in types" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select-input>
            <select-input v-if="!(hidden_fields && hidden_fields.includes('category'))" v-model="form.category_id"
                class="mr-2 w-full">
                <option :value="null">{{ __('Category') }}</option>
                <option v-for="s in categories" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select-input>
            <select-input v-if="!(hidden_fields && hidden_fields.includes('department'))" v-model="form.department_id"
                class="mr-2 w-full">
                <option :value="null">{{ __('Department') }}</option>
                <option v-for="s in departments" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select-input>
            <select-input v-model="form.priority_id" class=" mr-2 w-full">
                <option :value="null">{{ __('Priority') }}</option>
                <option v-for="s in priorities" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select-input>
            <select-input v-model="form.status_id" class="mr-2 w-full">
                <option :value="null">{{ __('Status') }}</option>
                <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select-input>
            <select-input-filter :placeholder="__('Assign To')" :onInput="doFilter" @focus="doFilter" :items="assignees"
                v-if="!(hidden_fields && hidden_fields.includes('assigned_to')) && user_access.ticket.update"
                v-model="form.assigned_to" class=" w-full">
            </select-input-filter>
            <!-- Date Filter: Start Date -->
            <input type="date" v-model="form.startDate" class="mr-2 w-full border border-gray-300 p-2 rounded"
                placeholder="DD MM YYYY" />

            <!-- Date Filter: End Date -->
            <input type="date" v-model="form.endDate" class="mr-2 w-full border border-gray-300 p-2 rounded"
                placeholder="DD MM YYYY" />
        </div>
        <div class="bg-white rounded-md shadow overflow-x-auto">
            <table class="min-w-full whitespace-nowrap ticket_list">
                <tr class="text-left font-bold">
                    <th v-for="(h, i) in headers" :key="i">
                        <span :class="{ 'sort': h.sort, 'active': form.field === h.name }, form.direction">{{ __(h.name)
                            }}
                            <span v-if="h.sort" class="icons">
                                <icon class="fill-gray-300"
                                    :class="{ 'fill-gray-800': (form.direction === 'desc' && form.field === h.value) }"
                                    name="up" @click="sort(h.value)" />
                                <icon class="fill-gray-300"
                                    :class="{ 'fill-gray-800': form.direction === 'asc' && form.field === h.value }"
                                    name="down" @click="sort(h.value)" />
                            </span>
                        </span>
                    </th>
                </tr>
                <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        #{{ ticket.uid }}
                        </Link>
                    </td>
                    <td class="border-t">
                        <Link class="s__details flex flex-col" :href="route('tickets.edit', ticket.uid || ticket.id)">
                        <span class="subject_t">{{ ticket.subject }}</span>
                        <span class="user__d flex text-xs items-center pt-1">
                            <span v-if="ticket.from" class="user__n flex items-center pr-4" title="From">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#FACC15" class="bi bi-send-check" viewBox="0 0 16 16">
                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372zm-2.54 1.183L5.93 9.363 1.591 6.602z"/>
                            <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/>
                            </svg>
                            {{ ticket.from }}
                            </span>
                            <span v-if="ticket.user" class="user__n flex items-center pr-4" title="Client">
                                <icon name="user" class="flex-shrink-0 h-3 fill-gray-400 pr-1" />
                                {{ ticket.user }}
                            </span>
                            <span v-if="ticket.assigned_to" class="user__n flex items-center pr-4" title="Assignee">
                                <icon name="user-check" class="flex-shrink-0 h-3 fill-gray-400 pr-1" />
                                {{ ticket.assigned_to }}
                            </span>
                        
                           
                        </span>
                        </Link>

                    </td>
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        {{ ticket.ticketType }}
                        </Link>
                    </td>
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        {{ ticket.priority }}
                        </Link>
                    </td>
                  
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        {{ ticket.status }}
                        </Link>
                    </td>
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        {{ __('error') === 'error' ? moment(ticket.created_at).format('DD MM YYYY, h:mm') :
                            moment(ticket.created_at).format('DD MM YYYY, h:mm') }}
                        </Link>
                    </td>
                    <td class="border-t">
                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="route('tickets.edit', ticket.uid || ticket.id)">
                        {{ __('error') === 'error' ? moment(ticket.updated_at).format('DD MM YYYY, h:mm') :
                            moment(ticket.updated_at).format('DD MMM YYYY, h:mm') }}
                        </Link>
                    </td>
                </tr>
                <tr v-if="tickets.data.length === 0">
                    <td class="border-t px-6 py-4" colspan="9">{{ __('No ticket found.') }}</td>
                </tr>
            </table>
        </div>
        <pagination class="mt-4" :links="tickets.links" />
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
import SelectInput from '@/Shared/SelectInput'
import SearchInput from '@/Shared/SearchInput'
import SelectInputFilter from '@/Shared/SelectInputFilter'
import moment from 'moment'
import axios from 'axios'

export default {
    metaInfo: { title: 'Tickets' },
    components: {
        SearchInput,
        Icon,
        Link,
        Head,
        Pagination,
        SelectInputFilter,
        SelectInput,
    },
    layout: Layout,
    props: {
        filters: Object,
        tickets: Object,
        assignees: Array,
        auth: Object,
        title: String,
        priorities: Array,
        statuses: Array,
        types: Array,
        categories: Array,
        departments: Array,
        hidden_fields: Object,
    },
    remember: 'form',
    data() {
        return {
            headers: [
                { name: 'Key', value: 'id', sort: true },
                { name: 'Subject', value: 'subject', sort: true },
                { name: 'Ticket Type', value: 'ticketType', sort: true },

                { name: 'Priority', value: 'priority_id', sort: true },
                { name: 'Status', value: 'status_id', sort: true },
                { name: 'Date', value: 'created_at', sort: true },
                { name: 'Updated', value: 'updated_at', sort: true },
            ],
            user_access: this.$page.props.auth.user.access,
            form: {
                search: this.filters.search,
                limit: this.filters.limit ?? 10,
                customer_id: this.filters.customer_id,
                field: this.filters.field,
                direction: this.filters.direction,
                priority_id: this.filters.priority_id ?? null,
                status_id: this.filters.status_id ?? null,
                type_id: this.filters.type_id ?? null,
                category_id: this.filters.category_id ?? null,
                department_id: this.filters.department_id ?? null,
                startDate: this.filters.startDate ?? '',  // Start date filter
                endDate: this.filters.endDate ?? '',      // End date filter
            },
        }
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function () {
                this.$inertia.get(this.route('tickets'), pickBy(this.form), { replace: true, preserveState: true })
            }, 150),
        },
    },
    methods: {
        doFilter(e) {
            axios.get(this.route('filter.assignees', { search: e.target.value })).then((res) => {
                this.assignees.splice(0, this.assignees.length, ...res.data);
            })
        },
        sort(field) {
            this.form.field = field;
            this.form.direction = this.form.direction === 'asc' ? 'desc' : 'asc';
        },
        reset() {
            this.form = mapValues(this.form, () => null)
        },
        uploadImportCSV(e) {
            if (e.target.files.length) {
                this.$inertia.form({ file: e.target.files[0] }).post(this.route('ticket.csv.import'))
            }
        }
    },
    created() {
        this.moment = moment;
    }
}
</script>
