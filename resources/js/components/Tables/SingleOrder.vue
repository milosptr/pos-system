<template>
  <div v-if="isEmpty" class="OrderSidebar mt-4">
    <div class="bg-gray-500 flex justify-between items-center text-white px-2">
      <div>Porudzbina #1</div>
      <div v-if="saved">17:00:00</div>
    </div>
    <div
      v-for="order in order.orders"
      :key="order.id"
      class="py-1 px-2 text-xl bg-gray-100 mt-2">
      <div class="font-semibold ">{{ order.name }}</div>
      <div class="flex justify-between items-center">
        <div>{{ order.qty}} x {{ order.price }},00</div>
        <div class="font-semibold">{{ order.qty * order.price }} RSD</div>
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
      saved: {
        type: Boolean,
        default: () => true
      }
    },
    computed: {
      isEmpty() {
        return this.order.hasOwnProperty('orders') && this.order.orders.length
      }
    },
    mounted() {
      console.log(this.order)
    }
  }
</script>
