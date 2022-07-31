<template>
  <div class="flex justify-end gap-4">
    <div>
      <label for="waiters" class="block text-sm font-medium text-gray-700">Waiter</label>
      <select
        name="waiters"
        class="block w-full pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        @change="updateReportFilters('waiter', $event.target.value)"
      >
        <option value="" selected>All</option>
        <option v-for="waiter in waiters" :key="waiter.id" :value="waiter.id">{{ waiter.name }}</option>
      </select>
    </div>
    <div>
      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
      <select
        name="status"
        class="block w-full pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        @change="updateReportFilters('status', $event.target.value)"
      >
        <option value="" selected>All</option>
        <option value="0">Refunded</option>
        <option value="1">Payed</option>
      </select>
    </div>
    <div class="w-64 relative text-sm">
      <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
      <litepie-datepicker
        i18n="sr"
        use-range
        separator=" to "
        :formatter="formatter"
        :shortcuts="customShortcuts"
        :auto-apply="false"
        v-model="date"
      />
    </div>
  </div>
</template>

<script>
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
    data: () => ({
      date: '',
      formatter: {
        date: 'YYYY-MM-DD',
        month: 'MMM'
      },
      customShortcuts,
      waiters: []
    }),
    watch: {
      date(val) {
        this.updateReportFilters('date', val)
      }
    },
    mounted() {
      console.log(dayjs());
      axios.get('/api/waiters')
        .then((res) => {
          this.waiters = res.data.data
        })
    },
    methods: {
      updateReportFilters(key, value) {
        this.$store.commit('setReportFilters', { key, value })
        this.$store.dispatch('getReports')
      }
    }
  }
</script>

<style scoped>
  select {
    height: 42px;
  }
</style>
