<template>
  <div class="w-full">
  <div class="flex flex-col sm:flex-row justify-end gap-4">
    <div v-if="!tabInvoices">
      <label for="inventory" class="block text-sm font-medium text-gray-700">Pretraga artikla</label>
      <div class="relative flex items-center">
        <input  @input="debounceInput" type="text" name="search" id="search" placeholder="Pretraga" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 sm:text-sm border-gray-300 rounded-md" />
        <div class="absolute inset-y-0 left-0 flex py-1.5 pr-1.5">
          <div class="inline-flex items-center px-2 text-sm font-sans font-medium text-gray-400"> <SearchIcon class="h-4 w-4" /> </div>
        </div>
      </div>
    </div>
    <div v-if="!tabInvoices">
      <label for="category" class="block text-sm font-medium text-gray-700">Kategorija</label>
      <select
        name="category"
        class="block w-full sm:w-48 pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        @change="updateReportFilters('category', $event.target.value)"
      >
        <option value="" selected>Sve</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
      </select>
    </div>
    <div v-if="tabInvoices">
      <label for="waiters" class="block text-sm font-medium text-gray-700">Konobar</label>
      <select
        name="waiters"
        class="block w-full pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        @change="updateReportFilters('waiter', $event.target.value)"
      >
        <option value="" selected>Svi</option>
        <option v-for="waiter in waiters" :key="waiter.id" :value="waiter.id">{{ waiter.name }}</option>
      </select>
    </div>
    <div v-if="tabInvoices">
      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
      <select
        name="status"
        class="block w-full pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        @change="updateReportFilters('status', $event.target.value)"
      >
        <option value="" selected>Sve</option>
        <option value="0">Stornirano</option>
        <option value="1">Naplaćeno</option>
        <option value="2">Na račun kuće</option>
      </select>
    </div>
    <div class="w-full sm:w-64 relative text-sm">
      <label for="date" class="block text-sm font-medium text-gray-700">Datum</label>
      <litepie-datepicker
        i18n="sr"
        use-range
        separator=" to "
        :formatter="formatter"
        :shortcuts="customShortcuts"
        :auto-apply="false"
        readonly
        aria-readonly="true"
        v-model="date"
      />
    </div>
  </div>
</div>
</template>

<script>
import { SearchIcon } from '@heroicons/vue/outline'
import axios from 'axios';

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
    components: { SearchIcon },
    props: {
      type: {
        type: String,
        default: () => 'reports'
      },
    },
    data: () => ({
      date: '',
      formatter: {
        date: 'YYYY-MM-DD',
        month: 'MMM'
      },
      customShortcuts,
      waiters: [],
      inventory: [],
      categories: [],
    }),
    watch: {
      date(val) {
        this.updateReportFilters('date', val)
      }
    },
    computed: {
      tabInvoices() {
        return !this.$store.getters.reportsActiveTab
      },
    },
    mounted() {
      const startOfMonth = dayjs().startOf('M').format('YYYY-MM-DD')
      const endOfMonth = dayjs().endOf('M').format('YYYY-MM-DD')
      const defaultDate = startOfMonth + ' to ' + endOfMonth
      this.updateReportFilters('date', defaultDate)

      axios.get('/api/waiters')
        .then((res) => {
          this.waiters = res.data.data
        })
      axios.get('/api/inventory')
        .then((res) => {
          this.inventory = res.data.data
        })
      axios.get('/api/categories')
        .then((res) => {
          this.categories = res.data.data
        })
    },
    methods: {
      updateReportFilters(key, value) {
        this.$store.commit('setReportFilters', { key, value })
        if(this.type === 'reports')
          this.$store.dispatch('getReports')
        if(this.type === 'invoices')
          this.$store.dispatch('getInvoices')
      },
      debounceInput: _.debounce(function(e) {
        this.updateReportFilters('q', e.target.value)
      }, 400)
    }
  }
</script>

<style scoped>
  select {
    height: 42px;
  }
</style>
