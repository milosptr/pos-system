<template>
  <div>
    <div class="-mt-12 mb-12 flex items-center justify-end gap-4">
      <div
        class="rounded-md bg-gray-200 px-4 py-2"
        :class="{ 'bg-indigo-500 text-white': activeTab === 2 }"
        @click="changeTab(2)">
        Uvoz
      </div>
    </div>
    <div class="flex flex-col items-end justify-between gap-4 lg:flex-row lg:gap-0">
      <div class="flex w-full items-end justify-end gap-4 lg:w-auto">
        <div
          class="w-full whitespace-nowrap rounded-md bg-gray-200 px-4 py-2 text-center lg:w-auto"
          :class="{ 'bg-indigo-500 text-white': !activeTab }"
          @click="changeTab(0)">
          Promet po danima
        </div>
        <div
          class="w-full whitespace-nowrap rounded-md bg-gray-200 px-4 py-2 text-center lg:w-auto"
          :class="{ 'bg-indigo-500 text-white': activeTab === 1 }"
          @click="changeTab(1)">
          Promet artikala
        </div>
      </div>
      <BackofficeReportsFilters v-if="activeTab !== 2" />
    </div>
    <BackofficeReportsStats v-if="activeTab !== 2" />
    <div v-if="!activeTab">
      <BackofficeReportsTable />
    </div>
    <div v-if="activeTab === 1">
      <BackofficeReportsInventoryTable />
    </div>
    <div v-if="activeTab === 2">
      <BackofficeImportedSales />
    </div>
  </div>
</template>

<script>
import BackofficeReportsFilters from './BackofficeReportsFilters.vue'
import BackofficeReportsStats from './BackofficeReportsStats.vue'
import BackofficeReportsTable from './BackofficeReportsTable.vue'
import BackofficeReportsInventoryTable from './BackofficeReportsInventoryTable.vue'
import OverviewSlideoverSidebar from '../Overview/OverviewSlideoverSidebar.vue'
import BackofficeImportedSales from './BackofficeImportedSales.vue'

export default {
  components: {
    BackofficeReportsFilters,
    BackofficeReportsStats,
    BackofficeReportsTable,
    OverviewSlideoverSidebar,
    BackofficeReportsInventoryTable,
    BackofficeImportedSales
  },
  computed: {
    activeTab() {
      return this.$store.getters.reportsActiveTab
    }
  },
  mounted() {
    this.$store.dispatch('getReports')
  },
  methods: {
    changeTab(tab) {
      this.$store.commit('resetReportFilters')
      const startOfMonth = dayjs().startOf('M').format('YYYY-MM-DD')
      const endOfMonth = dayjs().endOf('M').format('YYYY-MM-DD')
      const defaultDate = startOfMonth + ' to ' + endOfMonth
      this.$store.commit('setReportFilters', { key: 'date', value: defaultDate })
      this.$store.commit('setReportsActiveTab', tab)
      this.$store.dispatch('getReports')
    }
  }
}
</script>
