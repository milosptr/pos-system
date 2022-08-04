<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="$emit('close')">
      <div class="fixed inset-0" />

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                  <div class="px-4 sm:px-6">
                    <div class="flex items-start justify-between">
                      <DialogTitle class="text-2xl font-bold text-gray-900"> All Orders </DialogTitle>
                      <div class="ml-3 flex h-7 items-center">
                        <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="$emit('close')">
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="relative mt-6 flex-1 px-4 sm:px-6">
                    <div class="OrderSidebar overflow-x-hidden">
                      <SingleOrder
                        v-for="(order) in orders"
                        :key="order.id"
                        :order="order"
                        :title="order.table_name"
                        :boxBackground="false"
                        class="fs-18"
                        :class="{'text-red-500': order.status === 0}"
                      />
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script>
  import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
  import { XIcon } from '@heroicons/vue/outline'
  import SingleOrder from '../../Tables/SingleOrder.vue'

  export default {
    props: {
      show: {
        type: Boolean,
        default: () => true,
      }
    },
    components: {
      Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, XIcon, SingleOrder
    },
    data: () => ({}),
    computed: {
      activeTableOrders() {
        return this.$store.getters.activeTableOrders
      },
      orders() {
        const orders = []
        this.activeTableOrders.forEach((t) => {
          orders.push(...t.orders.map((o) => {
            o.table_name = t.name
            return o
          }))
        })
        return orders.sort(function(a,b){
          return new Date(b.created_at_full) - new Date(a.created_at_full);
        });
      },
    },
    mounted() {},
  }
</script>

<style >
  .SingleOrderItem {
    border-bottom: 1px solid #eee;
  }

  .fs-18 * {
    font-size: 18px!important;
    font-weight: 500;
  }

  @media (max-width: 450px) {
    .fs-18 * {
      font-size: 14px!important;
      line-height: 18px;
      font-weight: 500;
    }
  }
</style>
