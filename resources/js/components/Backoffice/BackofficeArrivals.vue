<template>
  <div class="flex flex-col">
    <div class="flex justify-end items-center gap-5">
      <div>
        <label for="date" class="block text-sm font-medium text-gray-700">Employee</label>
        <Select :list="[{id: 0, name: 'All employees'}, ...employees]" @select="filterEmployees" />
      </div>
      <div class="w-full sm:w-64 relative text-sm">
        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
        <litepie-datepicker
          i18n="sr"
          use-range
          separator=" to "
          :formatter="formatter"
          :auto-apply="false"
          readonly
          aria-readonly="true"
          v-model="date"
        />
      </div>
    </div>
    <div class="w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
      <div class="inline-block min-w-full py-2 align-middle">
        <div class="shadow-sm ring-1 ring-black ring-opacity-5">
          <table class="min-w-full border-separate" style="border-spacing: 0">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Date</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Check in</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Check out</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Duration</th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <tr
                v-for="(arrival, idx) in arrivals"
                :key="arrival.id"
                class="hover:bg-orange-50 cursor-pointer"
              >
                <td :class="[idx !== arrivals.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ formatDate(arrival.created_at) }}</td>
                <td :class="[idx !== arrivals.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-900']">{{ arrival.employee_name }}</td>
                <td :class="[idx !== arrivals.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-900']">{{ formatTime(arrival.check_in) }}</td>
                <td :class="[idx !== arrivals.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-900']">{{ formatTime(arrival.check_out) }}</td>
                <td :class="[idx !== arrivals.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-900']">{{ duration(arrival) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Select from '../common/Select.vue';

export default {
  components: { Select },
  data: () => ({
    arrivals: [],
    employees: [],
    date: '',
    employee: null,
    formatter: {
      date: 'YYYY-MM-DD',
      month: 'MMM'
    },
  }),
  watch: {
    date(val) {
      let filterQuery = ''
      if(val.length) {
        const dates = val.split(' to ')
        filterQuery = `from=${dates[0]}&to=${dates[1]}`
      }
      if(this.employee) {
        filterQuery += `&employee=${this.employee}`
      }
      this.getArrivals(filterQuery)
    }
  },
  mounted(){
    this.getEmployees()
    this.getArrivals()
  },
  methods: {
    filterEmployees(value) {
      this.employee = value.id ? value.id : null
      let filterQuery = parseInt(value.id) ? `employee=${value.id}` : null
      if(this.date.length) {
        const dates = this.date.split(' to ')
        filterQuery += `&from=${dates[0]}&to=${dates[1]}`
      }
      this.getArrivals(filterQuery)
    },
    getArrivals(filters = null) {
      fetch(`http://scheduler.test/public/arrivals?${filters ? filters : ''}`)
        .then(response => response.json())
        .then(result => { this.arrivals = result.data })
        .catch(error => console.log('error', error))
    },
    getEmployees() {
      fetch(`http://scheduler.test/public/employees`)
        .then(response => response.json())
        .then(result => { this.employees = result.data })
        .catch(error => console.log('error', error))
    },
    formatDate(date) {
      return dayjs(date).format('DD. MMM YYYY')
    },
    formatTime(date) {
      if(date)
        return dayjs(date).format('HH:mm:ss')
      return ''
    },
    duration(arrival) {
      if(arrival.check_in && arrival.check_out) {
        const checkIn = dayjs(arrival.check_in)
        const checkOut = dayjs(arrival.check_out)
        const diff = checkOut.diff(checkIn, 'seconds')
        const h = Math.floor(diff / 3600);
        const m = Math.floor(diff % 3600 / 60);
        const s = Math.floor(diff % 3600 % 60);
        const hDisplay = h > 0 ? (h + " h, ") : "";
        const mDisplay = m > 0 ? (m + " min, ") : "";
        const sDisplay = s > 0 ? (s + " sec") : "";
        return hDisplay + mDisplay + sDisplay;
      }
      return '/'
    }
  }
}
</script>
