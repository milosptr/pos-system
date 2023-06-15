<template>
  <div>
    <div
      v-for="(order, idx) in $store.getters.orders"
      :key="idx">
      {{ order }}
    </div>
  </div>
</template>
<script>
export default {
  name: 'BackofficeOrders',
  mounted() {
    this.$store.dispatch('getOrders')
  }
}
</script>
<style scoped></style>
