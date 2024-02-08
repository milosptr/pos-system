<template>
  <div>
    <div class="">
      <div class="text-right flex items-center gap-4">
        <ChevronLeftIcon
          class="w-6 cursor-pointer"
          @click="previousDate()" />
        <input
          type="date"
          v-model="date"
          :max="maxDate"
          @change="updateDate"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-48 sm:text-sm border-gray-300 rounded-md" />
        <ChevronRightIcon
          class="w-6 cursor-pointer"
          @click="nextDate()" />
      </div>
      <div class="mt-6 border border-solid border-gray-200 bg-white">
        <div class="grid grid-cols-5 font-semibold text-sm py-1 border-b border-solid border-gray-300 bg-gray-200">
          <!--          <div class="px-4">Datum</div>-->
          <div class="px-4">Sirovina</div>
          <div class="px-4 text-center">Prethodno stanje</div>
          <div class="px-4 text-center">Prodato</div>
          <div class="px-4 text-center">Uneto</div>
          <div class="px-4 text-center">Zavrsno stanje</div>
        </div>
        <div
          v-for="(item, index) in warehouse"
          :key="item.id"
          class="grid grid-cols-5 text-sm py-1"
          :class="{ 'bg-gray-100': index % 2 === 1 }">
          <!--          <div class="px-4">{{ dayjs(item.date).format('DD.MM.YYYY') }}</div>-->
          <div class="px-4">{{ item.warehouse.name }}</div>
          <div class="px-4 text-center">{{ item.previous_quantity }}</div>
          <div class="px-4 text-red-600 font-medium text-center">{{ item.sale_quantity }}</div>
          <div class="px-4 text-green-600 font-medium text-center">{{ item.import_quantity }}</div>
          <div
            class="px-4 text-center"
            :class="item.quantity < 0 && 'text-orange-500 font-medium'">
            {{ item.quantity }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import dayjs from 'dayjs'
import { ChevronLeftIcon, ChevronRightIcon, ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/outline'
export default {
  name: 'WarehouseSales',
  data: () => ({
    warehouse: [],
    date: dayjs().format('YYYY-MM-DD')
  }),
  components: {
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowUpIcon,
    ArrowDownIcon
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
      axios.get('/api/backoffice/warehouse-status?date=' + dayjs(this.date).format('YYYY-MM-DD')).then((response) => {
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
