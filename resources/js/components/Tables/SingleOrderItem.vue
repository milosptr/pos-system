<template>
  <div
    v-click-outside="() => { showDelete = false }"
    v-if="order.qty"
    class="SingleOrderItem relative"
    :class="[{'ShowDelete' : showDelete}, {'text-red-500': order.refund || order.storno}, {'bg-gray-400': showChangedClass && !still}, {'line-through': (order.refund && !routeInvoice) || order.storno}]"
    @click="shouldShowDelete"
  >
    <div class="font-semibold truncate">{{ order.name }}</div>
    <div class="flex justify-between items-center tbfs-1">
      <div><span class="font-semibold fs-14rem">{{ order.qty}}</span> <span class="font-semibold"> x </span> {{ $filters.formatPrice(order.price) }},00</div>
      <div class="font-semibold">{{ $filters.formatPrice(order.qty * order.price) }} RSD</div>
    </div>
    <div
      v-show="!order.refund"
      class="absolute right-0 top-0 -mr-24 h-full bg-red-500 w-20 flex items-center justify-center opacity-0"
      :class="{'ShowButton': !order.refund}"
      @click="refundItem"
    >
      <img :src="$filters.imgUrl('trash.svg')" alt="remove" width="32">
    </div>
    <div
      v-show="order.refund"
      class="bg-green-500 absolute right-0 top-0 -mr-24 h-full w-20 flex items-center justify-center opacity-0"
      :class="{'ShowButton': order.refund}"
      @click="refundItem"
    >
      <img :src="$filters.imgUrl('rotate-back.svg')" alt="remove" width="32">
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
      disableRefund: {
        type: Boolean,
        default: () => false
      },
      still: {
        type: Boolean,
        default: () => false
      },
    },
    data: () => ({
      showDelete: false,
      showChangedClass: false,
      routeInvoice: false,
    }),
    watch: {
      order: {
        handler(newValue, oldValue) {
          this.showChangedClass = true
          setTimeout(() => {
            this.showChangedClass = false
          }, 200);
        },
        deep: true
      }
    },
    mounted() {
      this.routeInvoice = this.$route.path === '/invoices'
    },
    methods: {
      shouldShowDelete() {
        if(this.$route.path.includes('table'))
         this.showDelete = !this.showDelete
      },
      refundItem() {
        this.$emit('refund')
        // this.order.refund = !this.order.refund
      }
    }
  }
</script>

<style scoped>
  .SingleOrderItem {
    transition: all .3s ease-in-out;
  }
  .ShowDelete {
    transform: translateX(-100px);
  }
  .ShowButton {
    animation: ShowButton .4s linear forwards;
  }
  .SingleOrderItemChanged {
    background: red;
  }

  .fs-14rem {
    font-size: 1.4rem;
  }

  @media(max-width: 1024px) {
    .tbfs-1 {
      font-size: 1rem;
    }

    .tbfs-1 .fs-14rem {
      font-size: 1.2rem;
    }
  }

  @keyframes ShowButton {
    0% {
      opacity: 0;
    }
    90% {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }
</style>
