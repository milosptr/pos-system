<template>
  <div class="flex flex-col">
      <div class="flex justify-end gap-4 mb-3">
        <div class="">
          <div>
            <div class="relative flex items-center">
              <input v-model="filters.q" type="text" name="search" id="search" placeholder="Search" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 sm:text-sm border-gray-300 rounded-md" @input="filterInventory(false)" />
              <div class="absolute inset-y-0 left-0 flex py-1.5 pr-1.5">
                <div class="inline-flex items-center px-2 text-sm font-sans font-medium text-gray-400"> <SearchIcon class="h-4 w-4" /> </div>
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <Select :list="categories" @select="filterInventory" />
        </div>
        <div v-if="filters.q || filters.category" class="border border-gray-100 rounded-sm py-2" @click="clearFilters">
          <XCircleIcon class="w-6 h-6" />
        </div>
      </div>
      <div class="-my-0 -mx-0  sm:-mx-6 lg:-mx-8 w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Price</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter whitespace-nowrap">Sold By</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Order</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Category</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Active</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Color</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <BackofficeInventoryItem
                  v-for="(item, idx) in inventory"
                  :key="item.id + '-' + idx"
                  :item="item"
                  :inventory="inventory"
                  :idx="idx"
                  :class="{'bg-gray-50': idx % 2 === 1 }"
                />
              </tbody>
              <tr>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 py-3 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Total</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell"></th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell text-center">{{ $filters.formatPrice(inventoryItemsPrice) }} RSD</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter whitespace-nowrap"></th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
</template>
<script>
import Select from '../common/Select.vue'
import { SearchIcon, XCircleIcon } from '@heroicons/vue/outline'
import BackofficeInventoryItem from './BackofficeInventoryItem.vue'

export default {
    name: 'BackofficeInventory',
    components: {
      Select,
      SearchIcon,
      XCircleIcon,
      BackofficeInventoryItem,
    },
    data: () => ({
      filters: {}
    }),
    computed: {
      inventory() {
        return this.$store.getters.inventory
      },
      categories() {
        return this.$store.getters.categories
      },
      inventoryItemsPrice() {
        return this.$store.getters.inventory.reduce((a, v) => a + v.price, 0)
      },
    },
    mounted() {
        this.$store.dispatch('getInventory', this.filters)
        this.$store.dispatch('getCategories')
    },
    methods: {
      filterInventory(cat) {
        if(cat)
          this.filters.category = cat.id
        this.$store.dispatch('getInventory', this.filters)
      },
      clearFilters() {
        document.querySelector('.select-component-text').innerText = 'Select'
        this.filters = {}
        this.$store.dispatch('getInventory', this.filters)
      },
    }
}
</script>
<style scoped>

</style>
