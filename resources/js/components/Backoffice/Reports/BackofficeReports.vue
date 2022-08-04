<template>
  <div>
    <div class="flex items-center justify-end gap-4 -mt-12 mb-12">
      <div class="bg-gray-200 px-4 py-2 rounded-md" :class="{'bg-indigo-500 text-white': !activeTab}" @click="changeTab(0)">Invoices</div>
      <div class="bg-gray-200 px-4 py-2 rounded-md" :class="{'bg-indigo-500 text-white': activeTab}" @click="changeTab(1)">Inventory</div>
    </div>
    <BackofficeReportsFilters />
    <BackofficeReportsStats />
    <div v-if="!activeTab">
      <BackofficeReportsTable />
      <OverviewSlideoverSidebar :isInvoice="true" />
    </div>
    <div v-else>
      <BackofficeReportsInventoryTable />
    </div>
  </div>
</template>

<script>
  import BackofficeReportsFilters from "./BackofficeReportsFilters.vue"
  import BackofficeReportsStats from './BackofficeReportsStats.vue'
  import BackofficeReportsTable from './BackofficeReportsTable.vue'
  import BackofficeReportsInventoryTable from './BackofficeReportsInventoryTable.vue'
  import OverviewSlideoverSidebar from '../Overview/OverviewSlideoverSidebar.vue'

  export default {
    components: { BackofficeReportsFilters, BackofficeReportsStats, BackofficeReportsTable, OverviewSlideoverSidebar, BackofficeReportsInventoryTable },
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
        this.$store.commit('setReportsActiveTab', tab)
        this.$store.dispatch('getReports')
      }
    }
  }
</script>
