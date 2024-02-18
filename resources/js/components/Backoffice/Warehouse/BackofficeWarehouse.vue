<template>
  <div>
    <div class="mb-10">
      <div class="sm:hidden">
        <label
          for="tabs"
          class="sr-only"
          >Select a tab</label
        >
        <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
        <select
          id="tabs"
          name="tabs"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
          @change="onTabChange($event.target.value)">
          >
          <option
            v-for="tab in tabs"
            :key="tab.name"
            :value="tab.id"
            :selected="tab.current">
            {{ tab.name }}
          </option>
        </select>
      </div>
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
            @click="onTabChange(tab.id)">
            {{ tab.name }}
          </div>
        </nav>
      </div>
    </div>
    <BackofficeWarehouseSales v-if="activeTab === 0" />
    <BackofficeWarehouseImports v-if="activeTab === 1" />
    <BackofficeWarehouseItems v-if="activeTab === 2" />
    <WarehouseStatusModal
      v-if="showUpdateStateModal"
      @close="showUpdateStateModal = false"
      @update="activeTab = 0" />
  </div>
</template>
<script>
import BackofficeWarehouseSales from './BackofficeWarehouseSales.vue'
import BackofficeWarehouseImports from './BackofficeWarehouseImports.vue'
import BackofficeWarehouseItems from './BackofficeWarehouseItems.vue'
import WarehouseStatusModal from '@/js/components/Modals/WarehouseStatusModal.vue'

export default {
  name: 'Warehouse',
  components: { WarehouseStatusModal, BackofficeWarehouseItems, BackofficeWarehouseSales, BackofficeWarehouseImports },
  data: () => ({
    tabs: [
      { id: 0, name: 'Prodaja' },
      { id: 1, name: 'Ulazi' },
      { id: 2, name: 'Sirovine' }
    ],
    activeTab: 0,
    showUpdateStateModal: false
  }),
  mounted() {
    // Use the "query" property to get the current tab from the URL.
    this.activeTab = parseInt(this.$route.query.tab) || 0
  },
  methods: {
    onTabChange(tab) {
      this.activeTab = parseInt(tab)
      // Use the "push" method to change the URL when the user selects a tab.
      this.$router.push({ query: { tab } })
    }
  }
}
</script>
