<template>
  <div class="ml-4 flex w-full flex-col">
    <div
      v-for="(orderItem, idx) in order.order"
      :key="idx"
      class="mb-2 flex flex-col rounded border px-8 py-2">
      <div class="mr-4">{{ orderItem.name }} x {{ orderItem.qty }}</div>
      <div class="mr-4">{{ orderItem.price }}</div>
    </div>
    <div>Total: {{ order.total }}</div>
  </div>
</template>
<script>
export default {
  name: 'OverviewOrder',
  props: {
    order: {
      type: Object,
      default: null
    }
  }
}
</script>
<style scoped></style>
