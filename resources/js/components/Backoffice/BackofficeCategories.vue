<template>
  <div class="flex flex-col">
    <div class="hidden sm:flex justify-between items-center">
      <nav
        class="flex space-x-4 cursor-pointer"
        aria-label="Tabs">
        <div
          v-for="tab in tabs"
          :key="tab.name"
          :class="[
            activeTab === tab.id ? 'bg-gray-200 text-gray-700' : 'text-gray-500 hover:text-gray-700',
            'rounded-md px-3 py-2 text-sm font-medium'
          ]"
          :aria-current="activeTab === tab.id ? 'page' : undefined"
          @click="changeTab(tab.id)">
          {{ tab.name }}
        </div>
      </nav>
    </div>
    <div class="w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
      <div class="inline-block min-w-full py-2 align-middle">
        <div class="shadow-sm ring-1 ring-black ring-opacity-5">
          <BackofficeCategoriesWarehouse v-if="activeTab === 1" />
          <table
            v-if="activeTab === 0"
            class="min-w-full border-separate"
            style="border-spacing: 0">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">
                  Id
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
                  Ime
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
                  Grupa
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">
                  Red
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
                  Štampa
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
                  Boja
                </th>
                <th
                  scope="col"
                  class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <BackofficeCategoriesItem
                v-for="(item, idx) in categories"
                :key="item.id"
                :item="item"
                :categories="categories"
                :idx="idx"
                :class="{ 'bg-gray-50': idx % 2 === 1 }" />
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import BackofficeCategoriesItem from './BackofficeCategoriesItem.vue'
import BackofficeCategoriesWarehouse from '@/js/components/Backoffice/BackofficeCategoriesWarehouse.vue'
export default {
  components: { BackofficeCategoriesWarehouse, BackofficeCategoriesItem },
  name: 'BackofficeCategories',
  data: () => ({
    activeTab: 0,
    tabs: [
      { id: 0, name: 'Kategorije' },
      { id: 1, name: 'Sirovine' }
    ]
  }),
  computed: {
    categories() {
      return this.$store.getters.categories
    }
  },
  mounted() {
    this.$store.dispatch('getCategories')
    this.activeTab = parseInt(this.$route?.query?.tab || 0)
  },
  methods: {
    changeTab(tab) {
      this.activeTab = parseInt(tab)
      this.$router.push({ query: { tab } })
    }
  }
}
</script>
<style scoped></style>
