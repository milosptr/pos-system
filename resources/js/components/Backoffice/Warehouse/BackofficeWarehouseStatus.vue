<template>
  <div>
    <div class="">
      <div class="text-right flex items-center gap-4">
        <div class="text-left">
          <label class="text-sm">Od datuma</label>
          <input
            type="date"
            v-model="from"
            :max="maxDate"
            @change="updateDate"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-48 sm:text-sm border-gray-300 rounded-md" />
        </div>
        <div class="text-left">
          <label class="text-sm">Do datuma</label>
          <input
            type="date"
            v-model="to"
            :max="maxDate"
            @change="updateDate"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-48 sm:text-sm border-gray-300 rounded-md" />
        </div>
      </div>
      <div class="mt-6 border border-solid border-gray-200 bg-white">
        <div class="grid grid-cols-5 font-semibold text-sm py-1 border-b border-solid border-gray-300 bg-gray-200">
          <div class="px-4">Sirovina</div>
          <div class="px-4">Kolicina</div>
          <div class="px-4">Jedinica</div>
        </div>
        <div
          v-for="(item, index) in warehouse"
          :key="item.id"
          class="grid grid-cols-5 text-sm py-1"
          :class="{ 'bg-gray-100': index % 2 === 1 }">
          <div class="px-4">{{ item.warehouse?.name }}</div>
          <div class="px-4">{{ item.quantity }}</div>
          <div class="px-4">{{ item.warehouse?.unit }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import dayjs from 'dayjs'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/outline'
export default {
  name: 'WarehouseStatus',
  data: () => ({
    warehouse: [],
    from: dayjs().subtract(1, 'month').format('YYYY-MM-DD'),
    to: dayjs().format('YYYY-MM-DD')
  }),
  components: {
    ChevronLeftIcon,
    ChevronRightIcon
  },
  mounted() {
    this.getWarehouseStatus()
  },
  computed: {
    maxDate() {
      return dayjs().format('YYYY-MM-DD')
    }
  },
  methods: {
    dayjs,
    getWarehouseStatus() {
      const from = dayjs(this.from).format('YYYY-MM-DD')
      const to = dayjs(this.to).format('YYYY-MM-DD')
      axios.get('/api/backoffice/warehouse-status/summarized?from=' + from + '&to=' + to).then((response) => {
        this.warehouse = response.data?.data ?? []
      })
    },
    updateDate() {
      this.getWarehouseStatus()
    },
    previousDate() {
      this.date = dayjs(this.date).subtract(1, 'day').format('YYYY-MM-DD')
      this.getWarehouseStatus()
    },
    nextDate() {
      this.date = dayjs(this.date).add(1, 'day').format('YYYY-MM-DD')
      if (dayjs(this.date).isAfter(dayjs().format('YYYY-MM-DD'))) {
        this.date = dayjs().format('YYYY-MM-DD')
        return
      }
      this.getWarehouseStatus()
    }
  }
}
</script>
