<template>
  <div>
    <div class="">
      <div class="flex flex-col sm:flex-row gap-10">
        <div class="w-full sm:w-64 relative text-sm">
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
        <div>
          <select
            @change="updateFilter('group_id', $event)"
            class="block w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="null">Sve</option>
            <option value="1">Kuhinja</option>
            <option value="0">Šank</option>
          </select>
        </div>
        <div>
          <select
            @change="updateFilter('category_id', $event)"
            class="block w-48 rounded-md capitalize border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <option value="null">Sve</option>
            <option
              v-for="category in warehouseCategories"
              :key="category.id"
              :value="category.id"
              class="capitalize">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div v-if="loading" class="flex items-center gap-2 text-gray-500">
          <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="text-sm">Učitavanje...</span>
        </div>
      </div>
      <div class="mt-6 border border-solid border-gray-200 bg-white">
        <div class="w-full">
          <div class="grid grid-cols-6 font-semibold text-sm">
            <div
              class="col-span-2 sm:col-span-1 w-64 flex-shrink-0 sm:w-full px-4 py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Sirovina</span>
            </div>
            <div
              class="w-full truncate px-2 sm:px-4 hidden sm:block py-1 border-b border-solid border-gray-300 bg-gray-200">
              Jedinica
            </div>
            <div class="w-full px-2 sm:px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="hidden sm:block">Prethodno stanje</span>
              <span class="block truncate sm:hidden whitespace-nowrap">P. stanje</span>
            </div>
            <div
              class="w-full truncate px-2 sm:px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Prodato</span>
            </div>
            <div
              class="w-full truncate px-2 sm:px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="">Uneto</span>
            </div>
            <div
              class="w-full truncate px-2 sm:px-4 text-center py-1 border-b border-solid border-gray-300 bg-gray-200">
              <span class="hidden sm:block">Zavrsno stanje</span>
              <span class="block truncate sm:hidden whitespace-nowrap">Z. stanje</span>
            </div>
          </div>
          <div
            v-for="(category, categoryId) in warehouse"
            :key="categoryId">
            <div
              class="w-full truncate px-2 sm:px-4 hidden sm:block py-1 border-b border-solid border-gray-300 bg-gray-200 text-sm font-semibold">
              {{ categoryName(category[0].category_id) }}
            </div>
            <div
              v-for="(item, index) in category"
              :key="item.id"
              class="grid grid-cols-6 text-sm"
              :class="{ 'bg-gray-100': index % 2 === 1 }">
              <div
                class="col-span-2 sm:col-span-1 w-full px-4 whitespace-nowrap py-1"
                :class="{ 'bg-gray-100': index % 2 === 1 }">
                <div class="w-full whitespace-break-spaces">
                  {{ item.warehouse.name }}
                </div>
              </div>
              <div
                class="w-full px-4 hidden sm:block py-1"
                :class="{ 'bg-gray-100': index % 2 === 1 }">
                {{ item.warehouse.unit }}
              </div>
              <div
                class="w-full px-4 text-center py-1"
                :class="{ 'bg-gray-100': index % 2 === 1 }">
                <div
                  class="flex items-center gap-3"
                  v-if="editItem && editItem.id === item.id">
                  <input
                    type="number"
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-24 sm:text-sm border-gray-300 rounded-md py-0.5"
                    :value="editItem.previous_quantity"
                    @input="editItem.recalculated = $event.target.value" />
                  <button
                    type="button"
                    class="relative text-center px-4 py-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    @click="editStartingQuantity">
                    Sačuvaj
                  </button>
                  <button
                    type="button"
                    @click="editItem = null"
                    class="relative text-center px-4 py-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Odustani
                  </button>
                </div>
                <div
                  v-else
                  class="px-4 flex items-center justify-end gap-1 sm:gap-3">
                  <div class="flex sm:w-16 justify-center items-center">
                    <div
                      v-if="parseFloat(item.recalculated_quantity) !== 0"
                      class="text-xs hidden sm:block">
                      {{ item.recalculated_quantity }}
                    </div>
                    <ArrowSmDownIcon
                      v-if="Number(item.recalculated_quantity) < 0"
                      class="w-5 h-5 text-red-500" />
                    <ArrowSmUpIcon
                      v-if="Number(item.recalculated_quantity) > 0"
                      class="w-5 h-5 text-green-500" />
                  </div>
                  <div
                    class="cursor-pointer sm:pr-16"
                    @click="editItem = { ...item }">
                    {{ item.previous_quantity }}
                  </div>
                </div>
              </div>
              <div
                class="w-full px-4 text-red-600 font-medium text-center py-1"
                :class="{ 'bg-gray-100': index % 2 === 1 }">
                {{ item.sale_quantity }}
              </div>
              <div
                class="w-full px-4 text-green-600 font-medium text-center py-1"
                :class="{ 'bg-gray-100': index % 2 === 1 }">
                {{ item.import_quantity }}
              </div>
              <div
                class="w-full px-4 text-center py-1"
                :class="[item.quantity < 0 && 'text-orange-500 font-medium', { 'bg-gray-100': index % 2 === 1 }]">
                {{ item.quantity }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import dayjs from 'dayjs'
import {
  ArrowSmUpIcon,
  ArrowSmDownIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  PencilIcon
} from '@heroicons/vue/outline'

const customShortcuts = () => {
  return [
    {
      label: 'Today',
      atClick: () => {
        const date = new Date();
        return [date, date];
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
        return [startOfWeek, date];
      }
    },
    {
      label: 'Last week',
      atClick: () => {
        const startOfWeek = dayjs().day(-14 + dayjs().day() + 1).format()
        const endOfWeek = dayjs().day(-7 + dayjs().day() + 1).format()
        return [startOfWeek, endOfWeek];
      }
    },
    {
      label: 'This Month',
      atClick: () => {
        const startOfMonth = dayjs().startOf('M');
        const endOfMonth = dayjs().endOf('M');
        return [startOfMonth, endOfMonth];
      }
    },
    {
      label: 'Last Month',
      atClick: () => {
        const startOfMonth = dayjs().subtract(1, 'month').startOf('M');
        const endOfMonth = dayjs().subtract(1, 'month').endOf('M');
        return [startOfMonth, endOfMonth];
      }
    },
  ];
}

export default {
  name: 'WarehouseSales',
  data: () => ({
    warehouse: [],
    warehouseCategories: [],
    date: dayjs().format('YYYY-MM-DD') + ' to ' + dayjs().format('YYYY-MM-DD'),
    formatter: {
      date: 'YYYY-MM-DD',
      month: 'MMM'
    },
    customShortcuts,
    category_id: null,
    group_id: null,
    draggedIndex: null,
    editItem: null,
    loading: false
  }),
  components: {
    PencilIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowSmUpIcon,
    ArrowSmDownIcon
  },
  mounted() {
    this.getWarehouseStatus()
    this.getWarehouseCategories()
  },
  computed: {
    // Pre-compute category name map for O(1) lookups instead of O(n) array.find
    categoryNameMap() {
      const map = {}
      this.warehouseCategories.forEach(cat => {
        map[cat.id] = cat.name
      })
      return map
    }
  },
  watch: {
    date(val) {
      if (val) {
        this.getWarehouseStatus()
      }
    }
  },
  methods: {
    dayjs,
    getWarehouseStatus() {
      let filters = `?date=${this.date}`
      if (this.category_id) {
        filters += `&category_id=${this.category_id}`
      }
      if (this.group_id) {
        filters += `&group_id=${this.group_id}`
      }
      this.loading = true
      axios.get('/api/backoffice/warehouse-status' + filters).then((response) => {
        const data = response.data?.data ?? []
        this.warehouse = this.groupBy(data, 'category_id')
      }).finally(() => {
        this.loading = false
      })
    },
    editStartingQuantity() {
      // Extract end date from range for recalculation
      const endDate = this.date.includes(' to ') ? this.date.split(' to ')[1] : this.date
      axios
        .post('/api/backoffice/warehouse-status/recalculate/' + this.editItem.warehouse_id, {
          quantity: parseFloat(this.editItem.recalculated) - parseFloat(this.editItem.previous_quantity),
          previous_quantity: parseFloat(this.editItem.previous_quantity),
          date: endDate
        })
        .then(() => {
          this.editItem = null
          this.getWarehouseStatus()
        })
    },
    getWarehouseCategories() {
      axios.get('/api/backoffice/warehouse-categories').then((response) => {
        this.warehouseCategories = response.data
      })
    },
    updateFilter(key, event) {
      this[key] = event.target.value === 'null' ? null : event.target.value
      this.getWarehouseStatus()
    },
    categoryName(id) {
      return this.categoryNameMap[id]
    },
    groupBy(array, key) {
      return array.reduce((result, currentValue) => {
        const group = currentValue[key]
        if (!result[group]) {
          result[group] = []
        }
        result[group].push(currentValue)
        return result
      }, {})
    }
  }
}
</script>
