<template>
  <div>
    <div class="">
      <div class="flex flex-col sm:flex-row gap-10">
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
        <!--        <div>-->
        <!--          <select-->
        <!--            class="block w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">-->
        <!--            <option>Kuhinja</option>-->
        <!--            <option>Šank</option>-->
        <!--          </select>-->
        <!--        </div>-->
        <!--        <div>-->
        <!--          <select-->
        <!--            class="block w-48 rounded-md capitalize border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">-->
        <!--            <option-->
        <!--              v-for="category in warehouseCategories"-->
        <!--              :key="category.id"-->
        <!--              :value="category.id"-->
        <!--              class="capitalize">-->
        <!--              {{ category.name }}-->
        <!--            </option>-->
        <!--          </select>-->
        <!--        </div>-->
      </div>
      <div class="mt-6 border border-solid border-gray-200 bg-white overflow-x-scroll">
        <div class="w-full">
          <div class="flex sm:grid sm:grid-cols-6 font-semibold text-sm">
            <div class="w-64 flex-shrink-0 sm:w-full px-4 py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Sirovina</span>
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 hidden sm:block py-1 border-b border-solid border-gray-300 bg-gray-200">
              Jedinica
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="hidden sm:block">Prethodno stanje</span>
              <span class="block sm:hidden whitespace-nowrap">P. stanje</span>
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Prodato</span>
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Uneto</span>
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="hidden sm:block">Zavrsno stanje</span>
              <span class="block sm:hidden whitespace-nowrap">Z. stanje</span>
            </div>
          </div>
          <div
            v-for="(item, index) in warehouse"
            :key="item.id"
            class="flex flex-shrink-0 sm:grid sm:grid-cols-6 text-sm"
            :class="{ 'bg-gray-100': index % 2 === 1 }">
            <div
              class="w-64 flex-shrink-0 sm:w-full px-4 whitespace-nowrap py-1"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              {{ item.warehouse.name }}
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 hidden sm:block py-1"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              {{ item.warehouse.unit }}
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              {{ item.previous_quantity }}
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-red-600 font-medium text-center py-1"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              {{ item.sale_quantity }}
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-green-600 font-medium text-center py-1"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              {{ item.import_quantity }}
            </div>
            <div
              class="w-24 flex-shrink-0 sm:w-full px-4 text-center py-1"
              :class="[item.quantity < 0 && 'text-orange-500 font-medium', { 'bg-gray-100': index % 2 === 1 }]">
              {{ item.quantity }}
            </div>
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
    warehouseCategories: [],
    date: dayjs().format('YYYY-MM-DD'),
    draggedIndex: null
  }),
  components: {
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowUpIcon,
    ArrowDownIcon
  },
  mounted() {
    this.getWarehouseStatus()
    this.getWarehouseCategories()
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
    },
    getWarehouseCategories() {
      axios.get('/api/backoffice/warehouse-categories').then((response) => {
        this.warehouseCategories = response.data
      })
    }
  }
}
</script>
