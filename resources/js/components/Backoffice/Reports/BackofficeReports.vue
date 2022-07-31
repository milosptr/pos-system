<template>
  <div>
    <BackofficeReportsFilters />
    <BackofficeReportsStats />
    <BackofficeReportsTable />
    <OverviewSlideoverSidebar :isInvoice="true" />
  </div>
</template>

<script>
  import BackofficeReportsFilters from "./BackofficeReportsFilters.vue"
  import BackofficeReportsStats from './BackofficeReportsStats.vue'
  import BackofficeReportsTable from './BackofficeReportsTable.vue'
  import OverviewSlideoverSidebar from '../Overview/OverviewSlideoverSidebar.vue'

  export default {
    components: { BackofficeReportsFilters, BackofficeReportsStats, BackofficeReportsTable, OverviewSlideoverSidebar },
    mounted() {
      this.$store.dispatch('getReports')
    },
  }
</script>
