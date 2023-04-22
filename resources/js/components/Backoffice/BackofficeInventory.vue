<template>
  <div class="flex flex-col">
      <div class="flex justify-end gap-4 mb-3">
        <a
          class="mr-auto flex items-center justify-center gap-3 px-3 py-1 cursor-pointer border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          @click="exportInventory"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#000000" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="120 168 120 216 148 216" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></polyline><line x1="52" y1="168" x2="88" y2="216" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></line><line x1="88" y1="168" x2="52" y2="216" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></line><path d="M176,212a25.2,25.2,0,0,0,15,5c9,0,17-3,17-13,0-16-32-9-32-24,0-8,6-13,15-13a25.2,25.2,0,0,1,15,5" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></path><path d="M48,128V40a8,8,0,0,1,8-8h96l56,56v40" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></path><polyline points="152 32 152 88 208 88" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"></polyline></svg>
        </a>
        <div>
          <div class="relative flex items-center">
            <input v-model="filters.q" type="text" name="search" id="search" placeholder="Pretraga" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 sm:text-sm border-gray-300 rounded-md" @input="debounceSearch" />
            <div class="absolute inset-y-0 left-0 flex py-1.5 pr-1.5">
              <div class="inline-flex items-center px-2 text-sm font-sans font-medium text-gray-400"> <SearchIcon class="h-4 w-4" /> </div>
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
      <div class="  w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table id="BackofficeInventoryTable" class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Artikal</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-center text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Cena</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter whitespace-nowrap">Jedinica</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Red</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Kategorija</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Aktivan</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Boja</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Sifra</th>
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
                <th scope="col" class="sticky top-0 z-10 border-b border-t border-gray-300 bg-gray-50 bg-opacity-75 py-3 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Ukupno</th>
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
      <OverviewSlideoverSidebarPricing />
    </div>
</template>
<script>
import Select from '../common/Select.vue'
import { SearchIcon, XCircleIcon } from '@heroicons/vue/outline'
import BackofficeInventoryItem from './BackofficeInventoryItem.vue'
import OverviewSlideoverSidebarPricing from './Overview/OverviewSlideoverSidebarPricing.vue'

export default {
    name: 'BackofficeInventory',
    components: {
      Select,
      SearchIcon,
      XCircleIcon,
      BackofficeInventoryItem,
      OverviewSlideoverSidebarPricing,
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
        return this.$store.getters.inventory.reduce((a, v) => a + (v.active ? v.price : 0), 0)
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
      exportInventory() {
        window.open('/api/backoffice/inventory/export', '_blank')
      },
      debounceSearch: _.debounce(function(e) {
        this.$store.dispatch('getInventory', this.filters)
      }, 500)
    }
}
</script>
<style scoped>

</style>
