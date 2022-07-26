<template>
  <TransitionRoot as="template" :show="!!activeOrder">
    <Dialog as="div" class="relative z-10" @close="$store.commit('setActiveOrder', null)">
      <div class="fixed inset-0" />

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                  <div class="px-4 sm:px-6">
                    <div class="flex items-start justify-between">
                      <DialogTitle class="text-2xl font-bold text-gray-900"> {{ activeOrder?.name }} </DialogTitle>
                      <div class="ml-3 flex h-7 items-center">
                        <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="$store.commit('setActiveOrder', null)">
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="relative mt-6 flex-1 px-4 sm:px-6">
                    <div v-if="activeOrder" class="OrderSidebar overflow-x-hidden">
                      <SingleOrder
                        v-for="order in activeOrder.orders"
                        :key="order.id"
                        :order="order"
                        :showOrderLine="false"
                        :boxBackground="false"
                        class="fs-18"
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
    components: {
      Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, XIcon, SingleOrder
    },
    data: () => ({}),
    computed: {
      activeOrder() {
        return this.$store.getters.activeOrder
      }
    },
    mounted() {},
  }
</script>

<style >
  .SingleOrderItem {
    border-top: 1px solid #eee;
  }

  .fs-18 * {
    font-size: 18px!important;
    font-weight: 500;
  }
</style>