<template>
  <div>
    <div class="sm:flex sm:items-end">
      <div class="flex flex-col md:flex-row gap-4 w-full">
        <div class="w-full md:w-56">
          <label for="clients" class="block text-sm font-medium leading-6 text-gray-900">Klijent</label>
          <select id="clients" @change="updateFilterClient" name="clients" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="null">Svi</option>
            <option v-for="client in bankAccounts" :key="client.id" :value="client.id">{{client.name}}</option>
          </select>
        </div>
        <div class="w-full md:w-32">
          <label for="filterStatus" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
          <select id="filterStatus" @change="updateFilterStatus" name="filterStatus" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="null">Sve</option>
            <option value="0">Neplaćeno</option>
            <option value="1">Plaćeno</option>
          </select>
        </div>
        <div class="w-full md:w-[240px]">
          <label for="clients" class="block text-sm font-medium leading-6 text-gray-900">Datum transakcije</label>
          <litepie-datepicker
            i18n="sr"
            use-range
            separator=" to "
            :formatter="formatter"
            :shortcuts="customShortcuts"
            :auto-apply="false"
            readonly
            aria-readonly="true"
            v-model="filters.date"
          />
        </div>
        <div class="w-full md:w-48">
          <label for="sortBy" class="block text-sm font-medium leading-6 text-gray-900">Sortiraj po</label>
          <select id="sortBy" @change="updateFilterSort" name="sortBy" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="transaction_date" selected>Datum prometa</option>
            <option value="payment_deadline">Datum valute</option>
            <option value="processed_at">Datum transakcije</option>
          </select>
        </div>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <div @click="addInvoiceModal = true" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Dodaj račun</div>
      </div>
    </div>
    <div class="mx-auto mt-6">
      <table class="w-full text-left bg-white border-l border-r border-gray-200">
        <thead class="sr-only">
          <tr>
            <th>Amount</th>
            <th class="hidden sm:table-cell">Client</th>
            <th>More details</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="incomingInvoices.length">
            <tr class="text-sm leading-6 text-gray-900 bg-gray-50 border-b border-t border-gray-200">
              <th scope="colgroup" colspan="3" class="relative isolate py-2 px-4 font-semibold">
                Neplaćeno
              </th>
            </tr>
            <BankInvoiceItem v-for="invoice in incomingInvoices" :invoice="invoice" :key="invoice.id" @updateInvoiceStatus="fetchInvoices()" />
            <tr class="text-sm leading-6 text-gray-900 border-b border-t border-gray-200">
              <th scope="colgroup" colspan="3" class="relative isolate py-2 px-4 font-semibold">
                <span class="mr-2">Total:</span>{{ totalIncomingInvoices }} RSD
              </th>
            </tr>
          </template>
          <template v-if="historyInvoices.length">
            <tr class="text-sm leading-6 text-gray-900 border-b border-t border-gray-200 bg-gray-50">
              <th scope="colgroup" colspan="3" class="relative isolate py-2 px-4 font-semibold">
                Istorija
              </th>
            </tr>
            <BankInvoiceItem v-for="invoice in historyInvoices" :invoice="invoice" :key="invoice.id" @updateInvoiceStatus="fetchInvoices()" />
            <tr class="text-sm leading-6 text-gray-900 border-b border-t border-gray-200">
              <th scope="colgroup" colspan="3" class="relative isolate py-2 px-4 font-semibold">
                <span class="mr-2">Total:</span>{{ totalHistoryInvoices }} RSD
              </th>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <AddBankInvoiceModal v-if="addInvoiceModal" @addInvoice="addNewInvoice" @close="addInvoiceModal = false" />
  </div>
</template>

<script>
  import BankInvoiceItem from './BankInvoiceItem.vue'
  import AddBankInvoiceModal from './AddBankInvoiceModal.vue'
  import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'

  const customShortcuts = () => {
    return [
      {
        label: 'Today',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate())),
            date
          ];
        }
      },
      {
        label: 'Tomorrow',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() + 1)),
            date
          ];
        }
      },
      {
        label: 'Yesterday',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'This week',
        atClick: () => {
          const date = new Date();
          const startOfWeek = dayjs().day(-7 + dayjs().day() + 1).format()
          return [
            startOfWeek,
            date
          ];
        }
      },
      {
        label: 'Last week',
        atClick: () => {
          const startOfWeek = dayjs().day(-14 + dayjs().day() + 1).format()
          const endOfWeek = dayjs().day(-7 + dayjs().day() + 1).format()
          return [
            startOfWeek,
            endOfWeek
          ];
        }
      },
      {
        label: 'This Month',
        atClick: () => {
          const startOfMonth = dayjs().startOf('M');
          const endOfMonth = dayjs().endOf('M');
          return [
            startOfMonth,
            endOfMonth
          ];
        }
      },
      {
        label: 'Last Month',
        atClick: () => {
          const startOfMonth = dayjs().subtract(1, 'month').startOf('M');
          const endOfMonth = dayjs().subtract(1, 'month').endOf('M');
          return [
            startOfMonth,
            endOfMonth
          ];
        }
      },
      {
        label: 'Last 6 Months',
        atClick: () => {
          const startOfMonth = dayjs().subtract(5, 'month').startOf('M');
          const endOfMonth = dayjs().endOf('M')
          return [
            startOfMonth,
            endOfMonth
          ];
        }
      },
      {
        label: 'This Year',
        atClick: () => {
          const startOfYear = dayjs().startOf('year')
          const endOfYear = dayjs().endOf('year')
          return [
            startOfYear,
            endOfYear
          ];
        }
      },
    ];
  }

  export default {
    components: {
      AddBankInvoiceModal,
      BankInvoiceItem,
      Switch,
      SwitchGroup,
      SwitchLabel
    },
    data: () => ({
      filters: {
        client_account: null,
        sort: 'transaction_date',
        date: {
          from: dayjs().startOf('M').format('YYYY-MM-DD'),
          to: dayjs().endOf('M').format('YYYY-MM-DD'),
        },
        status: null,
      },
      bankAccounts: [],
      incomingInvoices: [],
      historyInvoices: [],
      addInvoiceModal: false,
      customShortcuts,
      formatter: {
        date: 'YYYY-MM-DD',
        month: 'MMM'
      },
    }),
    watch: {
      filters: {
        handler() {
          const searchParams = this.parseFilters()
          this.fetchInvoices(searchParams)
        },
        deep: true
      }
    },
    computed: {
      totalIncomingInvoices() {
        return this.$filters.formatPrice(this.incomingInvoices.reduce((acc, invoice) => acc + invoice.amount, 0))
      },
      totalHistoryInvoices() {
        return this.$filters.formatPrice(this.historyInvoices.reduce((acc, invoice) => acc + invoice.amount, 0))
      }
    },
    mounted() {
      this.fetchInvoices()
      this.fetchBankAccounts()
    },
    methods: {
      fetchBankAccounts() {
        axios.get('/api/bank-accounts')
          .then((response) => {
            this.bankAccounts = response.data
          })
      },
      fetchInvoices(filters = null) {
        axios.get(`/api/bank-invoices${filters ? `?${filters}` : ''}`)
          .then((response) => {
            this.incomingInvoices = response.data.incomingInvoices
            this.historyInvoices = response.data.historyInvoices
          })
      },
      addNewInvoice() {
        this.fetchInvoices()
        this.addInvoiceModal = false
      },
      updateFilterSort(event) {
        this.filters.sort = event.target.value
      },
      updateFilterStatus(event) {
        if(event.target.value === 'null') {
          this.filters.status = null
        }
        else {
          this.filters.status = event.target.value
        }
      },
      updateFilterClient(event) {
        if(event.target.value === 'null') {
          this.filters.client_account = null
        }
        else {
          this.filters.client_account = event.target.value
        }
      },
      parseFilters() {
        const searchParams = new URLSearchParams()
          Object.keys(this.filters).forEach((key) => {
            if (this.filters[key]) {
              if (key === 'date') {
                if(this.filters[key].from)
                  searchParams.append('date_from', this.filters[key].from)
                if(this.filters[key].to)
                  searchParams.append('date_to', this.filters[key].to)
              } else {
                searchParams.append(key, this.filters[key])
              }
            }
          })
          return searchParams.toString()
      }
    }
  }
</script>
