<template>
  <div class="flex flex-col">
    <div class="flex justify-end items-center gap-5">
      <div>
        <label for="date" class="block text-sm font-medium text-gray-700">Occupation</label>
        <Select :preselected="occupation" :list="occupations" @select="filterOccupation" />
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
    <div class="mt-8 flow-root">
      <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle relative">
          <table class="min-w-full border-separate border-spacing-0">
            <thead>
              <tr>
                <th v-for="(emlpoyee, idx) in numberOfColumns" :key="idx" scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">{{ emlpoyee.name }}</th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <tr
                v-for="(date, idx) in numberOfRows"
                :key="idx"
                class="hover:bg-orange-50 cursor-pointer"
              >
                <td v-for="(key, jdx) in numberOfColumns" :key="jdx" :class="['border-b border-gray-200 whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">
                  <div v-html="printRow(key.name, date)" class="text-gray-600 text-sm"></div>
                </td>
              </tr>
              <tr>
                <th v-for="(emlpoyee, idx) in numberOfColumns" :key="idx" scope="col" class="sticky bottom-5 z-10 border-t border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">
                  {{ printTotal(emlpoyee.name) }}
                </th>
              </tr>
              <tr>
                <th colspan="10" class="bg-gray-100 sticky bottom-0 z-[9] w-full h-5"></th>
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
    date: '',
    occupation: 0, // sank: 0, kuhinja: 1
    occupations: [{ id: 0, name: 'Šank' }, { id: 1, name: 'Kuhinja' }],
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
        filterQuery = `from=${dates[0]}&to=${dates[1]}&occupation=${this.occupation}`
      }

      this.getArrivals(filterQuery)
    }
  },
  mounted(){
    this.getArrivals()
  },
  computed: {
    numberOfColumns() {
      if(this.arrivals?.employees)
        return [{ name: 'Date' }, ...this.arrivals.employees]
      return [{}]
    },
    numberOfRows() {
      if(this.arrivals?.data)
        return Object.keys(this.arrivals.data).reverse()
      return []
    }
  },
  methods: {
    getArrivals(filters = null) {
      // fetch(`http://192.168.200.30:81/public/arrivals?${filters ? filters : 'occupation=0'}`)
      fetch(`http://192.168.200.30:81/public/arrivals?${filters ? filters : 'occupation=0'}`)
        .then(response => response.json())
        .then(result => { this.arrivals = result })
        .catch(error => console.log('error', error))
    },
    filterOccupation({id}) {
      this.occupation = id
      let filterQuery = `occupation=${id}`
      if(this.date.length) {
        const dates = this.date.split(' to ')
        filterQuery += `&from=${dates[0]}&to=${dates[1]}`
      }
      this.getArrivals(filterQuery)
    },
    formatTime(date) {
      if(date)
        return dayjs(date, 'HH:mm:ss').format('HH:mm')
      return ''
    },
    printRow(key, date) {
      if(key === 'Date')
        return `${dayjs(date).format("DD.MM.YY ddd")}`
      try {
        const checkins = this.arrivals.data[date][key]
        if(checkins) {
          return checkins.map((c) => {
            if(c.check_out)
              return `
                <div class="${c.auto_checkout ? 'text-orange-500' : ''}">
                  <div>${c.total} <span class="text-gray-400 ${c.auto_checkout ? 'text-orange-500' : ''}">(${this.formatTime(c.check_in)} - ${this.formatTime(c.check_out)})</span></div>
                </div>`
            return `<div>${c.total} <span class="text-red-400">(${this.formatTime(c.check_in)} - ?)</span></div>`
          })
          .join('')
        }
      } catch (e) {
        console.log(key,date)
        return 'Error here'
      }
      return ''
    },
    printTotal(key) {
      if(key === 'Date')
        return 'Total'
      try {
        const checkins = this.arrivals?.data ? Object.values(this.arrivals.data).map((t) => t[key]).filter((c) => c !== undefined).flat() : []
        if(checkins.length) {
          const fullDays = checkins
          .filter((value, index, self) => {
            return self.findIndex(v => v.created_date === value.created_date) === index;
          })
          const totalInMinutes = checkins.reduce((accumulator, currentValue) => this.parseMinutes(currentValue.total) + accumulator, 0)
          if(!fullDays.length)
            return ''
          return `${this.toHoursAndMinutes(totalInMinutes)} (${fullDays.length}-d)`
        }
      } catch(e) {
        console.log(e)
      }
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
      return ''
    },
    parseMinutes(time) {
      const hours = time.split(':')[0]
      return parseInt(time.split(':')[1]) + parseInt(hours) * 60
    },
    toHoursAndMinutes(totalMinutes) {
      const hours = Math.floor(totalMinutes / 60);
      const minutes = totalMinutes % 60;
      return `${hours < 10 ? '0' + hours : hours}:${minutes < 10 ? '0' + minutes : minutes}`;
    }
  }
}
</script>
