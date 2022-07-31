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
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'Last week',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'This Month',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'Last Month',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'Last 6 Months',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'This Year',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
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
      console.log(this.$dayjs(new Date()));
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
