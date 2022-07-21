<template>
  <div v-if="order.order.length" class="mt-4">
    <div v-if="showOrderLine" class="bg-gray-500 flex justify-between items-center text-white px-2">
      <div>Porudzbina #{{ index }}</div>
      <div v-if="order.created_at">{{ order.created_at }}</div>
    </div>
    <div
      v-for="o in order.order"
      :key="o.id"
      class="py-1 px-2 text-xl bg-gray-100 mt-2">
      <div class="font-semibold ">{{ o.name }}</div>
      <div class="flex justify-between items-center">
        <div>{{ o.qty}} x {{ o.price }},00</div>
        <div class="font-semibold">{{ o.qty * o.price }} RSD</div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      order: {
        type: Object,
        default: () => {}
      },
      index: {
        type: Number,
        default: () => 1
      },
      saved: {
        type: Boolean,
        default: () => true
      },
      showOrderLine: {
        type: Boolean,
        default: () => true
      },
    },
  }
</script>
