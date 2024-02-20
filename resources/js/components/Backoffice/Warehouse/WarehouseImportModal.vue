<template>
  <div class="fixed top-0 left-0 z-50 w-full h-full flex items-center justify-center">
    <div
      class="absolute left-0 top-0 bg-black bg-opacity-50 w-full h-screen z-40"
      @click="$emit('close')" />
    <div class="relative w-full h-2/3 md:w-2/3 z-50 bg-white rounded-lg shadow-lg flex flex-col sm:flex-row">
      <div class="w-full sm:w-[250px] sm:h-full bg-gray-50 py-6 px-3">
        <div class="text-lg font-semibold leading-none py-1 mb-3 px-3">Uvoz</div>
        <div>
          <div class="pb-4 mb-3 border-solid border-b border-gray-300">
            <input
              type="date"
              v-model="date"
              :max="new Date().toISOString().split('T')[0]"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
          </div>
          <div
            v-for="item in itemsToSave"
            :key="item.id"
            @click="removeItemToSave(item.id)">
            <div
              class="flex flex-grow-0 justify-between items-center py-1 hover:bg-red-200 hover:text-red-600 rounded-md px-3 cursor-pointer">
              <div class="w-full text-sm capitalize truncate">{{ item?.name?.toLowerCase() }}</div>
              <div class="text-sm pl-3">{{ item.quantity }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="w-full flex flex-col justify-between p-6 overflow-y-scroll">
        <div>
          <div class="flex justify-between items-center gap-3 mb-6">
            <div class="text-lg font-semibold leading-none mb-0 py-1">Kategorije</div>
            <div
              v-if="selectedCategory !== null"
              class="w-24 bg-gray-100 border border-1 border-gray-400 text-center rounded-md cursor-pointer"
              @click="clear">
              Nazad
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            <div
              v-if="selectedCategory === null"
              v-for="item in categories"
              :key="item.id"
              class="py-10 bg-sky-100 hover:bg-sky-200/70 duration-300 rounded-md text-sm font-semibold text-center uppercase cursor-pointer"
              :style="`order: ${item.order + 1}`"
              @click="selectCategory(item.id)">
              {{ item?.name?.toLowerCase() }}
            </div>
            <div
              v-if="selectedCategory !== null"
              v-for="item in warehoueItems"
              :key="item.id"
              class="py-10 px-4 bg-orange-100 duration-300 rounded-md text-sm font-semibold text-center uppercase"
              :class="{ 'bg-green-200': item.quantity > 0 }"
              :style="`order: ${item.order + 1}`">
              <div>{{ item?.name?.toLowerCase() }}</div>
              <div class="mt-4 flex items-center gap-2">
                <div
                  class="p-2 text-xl font-normal leading-none cursor-pointer"
                  @click="decrease(item.id)">
                  &minus;
                </div>
                <input
                  type="text"
                  :value="item?.quantity ?? 0"
                  @keydown="handleKeyDown($event, item.id)"
                  @change="handleInput($event, item.id)"
                  class="shadow-sm font-normal focus:ring-indigo-500 focus:border-indigo-500 text-center block w-full sm:text-sm border-gray-300 rounded-md" />
                <div
                  class="p-2 text-xl font-normal leading-none cursor-pointer"
                  @click="increase(item.id)">
                  &plus;
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-end">
          <div
            v-if="itemsToSave.length"
            @click="save"
            class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ saving ? 'Čuvanje..' : 'Sačuvaj' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ChevronLeftIcon } from '@heroicons/vue/outline'

export default {
  name: 'WarehouseImportModal',
  components: {
    ChevronLeftIcon
  },
  data: () => ({
    date: new Date().toISOString().split('T')[0],
    selectedCategory: null,
    categories: [],
    warehoueItems: [],
    itemsToSave: [],
    saving: false
  }),
  mounted() {
    this.getWarehouseCategories()
  },
  methods: {
    clear() {
      this.selectedCategory = null
      this.warehoueItems = []
    },
    increase(id) {
      this.warehoueItems = this.warehoueItems.map((item) => {
        if (item.id === id) {
          const current = item?.quantity ?? 0
          item.quantity = current + 1
        }
        return item
      })
      const item = this.itemsToSave.find((item) => item.id === id)
      const warehouse = this.warehoueItems.find((item) => item.id === id)
      if (item) {
        item.quantity += 1
      } else {
        this.itemsToSave.push({ id, name: warehouse.name, quantity: 1 })
      }
    },
    decrease(id) {
      this.warehoueItems = this.warehoueItems.map((item) => {
        if (item.id === id) {
          const current = item?.quantity ?? 0
          if (current > 0) {
            item.quantity = current - 1
          }
        }
        return item
      })
      const item = this.itemsToSave.find((item) => item.id === id)
      if (item) {
        if (item.quantity > 0) {
          item.quantity -= 1
        }
      }
    },
    handleKeyDown(event, id) {
      const charCode = event.key.charCodeAt(0)
      if (event.key === 'ArrowUp') {
        event.preventDefault()
        this.increase(id)
        return
      } else if (event.key === 'ArrowDown') {
        event.preventDefault()
        this.decrease(id)
        return
      }
      if (event.key === 'Backspace') {
        return
      }
      if ((charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122)) {
        event.preventDefault()
      }
    },
    handleInput(event, id) {
      const item = this.itemsToSave.find((item) => item.id === id)
      const warehouse = this.warehoueItems.find((item) => item.id === id)
      let value = parseFloat(event.target.value)
      if (isNaN(value)) {
        value = 0
      }
      if (item) {
        item.quantity = value
      } else {
        this.itemsToSave.push({ id, name: warehouse.name, quantity: value })
      }
      this.warehoueItems = this.warehoueItems.map((item) => {
        if (item.id === id) {
          item.quantity = value
        }
        return item
      })
      console.log(event.target.value, value)
    },
    removeItemToSave(id) {
      this.itemsToSave = this.itemsToSave.filter((item) => item.id !== id)
      this.warehoueItems = this.warehoueItems.map((item) => {
        if (item.id === id) {
          item.quantity = 0
        }
        return item
      })
    },
    selectCategory(id) {
      this.selectedCategory = id
      this.getWarehouseItemsForCategory()
    },
    getWarehouseCategories() {
      axios.get('/api/backoffice/warehouse-categories').then((response) => {
        this.categories = response.data ?? []
      })
    },
    getWarehouseItemsForCategory() {
      axios.get(`/api/backoffice/warehouse/category/${this.selectedCategory}`).then((response) => {
        this.warehoueItems = response.data?.data ?? []
        console.log(this.warehoueItems)
        this.warehoueItems = this.warehoueItems.map((item) => {
          if (this.itemsToSave.some((i) => i.id === item.id)) {
            item.quantity = this.itemsToSave.find((i) => i.id === item.id).quantity
          }
          return item
        })
      })
    },
    save() {
      const data = this.itemsToSave.map((item) => {
        return {
          warehouse_id: item.id,
          quantity: item.quantity,
          type: 0,
          comment: 'Manual import',
          date: this.date,
          created_at: this.date
        }
      })
      this.saving = true
      Promise.all(
        data.map((item) => {
          return axios.post('/api/backoffice/warehouse-status', item)
        })
      )
        .then(() => {
          this.$emit('close')
          this.$emit('update')
          this.clear()
          this.saving = false
        })
        .catch(() => {
          this.clear()
          this.saving = false
          alert('Doslo je do greske!')
        })
    }
  }
}
</script>
