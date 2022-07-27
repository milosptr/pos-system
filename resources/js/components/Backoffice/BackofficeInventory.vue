<template>
  <div class="flex flex-col">
      <div class="flex justify-end gap-4 mb-3 -mx-4 sm:-mx-6 lg:-mx-8">
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
      <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                  <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>                  <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Price</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Sold By</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Category</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-for="(item, idx) in inventory" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="{'bg-gray-50': idx % 2 === 1 }">
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ idx + 1 }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">{{ item.name }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.price }} RSD</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ soldByText(item.sold_by) }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.category_name }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</template>
<script>
import Select from '../common/Select.vue'
import { SearchIcon, XCircleIcon } from '@heroicons/vue/outline'

export default {
    name: 'BackofficeInventory',
    components: {
      Select,
      SearchIcon,
      XCircleIcon,
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
        this.filters = {}
        this.$store.dispatch('getInventory', this.filters)
      },
      soldByText(id) {
        if(id === 1)
          return 'PP'
        if(id === 2)
          return 'KG'
        return 'KOM'
      }
    }
}
</script>
<style scoped>

</style>
