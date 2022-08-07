<template>
  <div class="mt-5">

  </div>
</template>

<script>
export default {
  name: 'BackofficeReports',
  data: () => ({
    direction: 'horizontal',
    margin: {
      left: 0,
      top: 20,
      right: 20,
      bottom: 0
    },
    size: { width: 100, height: 500 },
  }),
  computed: {
    invoices() {
      return this.$store.getters.reports?.invoices.map((i) => ({ date: dayjs(i.date).format('DD MMM YY'), total: parseInt(i.total)}))
    }
  },
  mounted() {
    this.size.width = document.getElementById('ChartWrapper').offsetWidth
  }
}
</script>
