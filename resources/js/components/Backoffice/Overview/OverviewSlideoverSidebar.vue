<template>
  <TransitionRoot as="template" :show="!!activeOrder">
    <Dialog as="div" class="relative z-30" @close="$store.commit('setActiveOrder', null)">
      <div class="fixed inset-0" />

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                  <div class="px-4 sm:px-6">
                    <div class="flex items-start justify-between">
                      <DialogTitle class="text-2xl font-bold text-gray-900"> {{ title }} </DialogTitle>
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
                        v-for="(order, index) in orders"
                        :key="order.id"
                        :order="order"
                        :index="orders.length - index"
                        :showOrderLine="!isInvoice"
                        :boxBackground="false"
                        class="fs-18"
                        :class="[{'text-red-500': isInvoice && order.status === 0}, {'text-indigo-500': isInvoice && order.status === 2}]"
                      />
                    </div>
                  </div>
                  <div v-if="isThirdPartyInvoice && activeOrder" class="px-4 sm:px-6 pt-4 border-t border-gray-200">
                    <button
                      type="button"
                      class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      @click="showDeleteModal = true"
                    >
                      <TrashIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                      Izbriši
                    </button>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <ThirdPartyInvoiceDeleteModal
    :show="showDeleteModal"
    :invoiceNumber="activeOrder ? activeOrder.invoice_number : ''"
    @close="showDeleteModal = false"
    @delete="handleDelete"
  />
</template>

<script>
  import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
  import { XIcon, TrashIcon } from '@heroicons/vue/outline'
  import SingleOrder from '../../Tables/SingleOrder.vue'
  import ThirdPartyInvoiceDeleteModal from '../../Modals/ThirdPartyInvoiceDeleteModal.vue'

  export default {
    props: {
      isInvoice: {
        type: Boolean,
        default: () => false,
      },
      isThirdPartyInvoice: {
        type: Boolean,
        default: () => false,
      },
    },
    components: {
      Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, XIcon, TrashIcon, SingleOrder, ThirdPartyInvoiceDeleteModal
    },
    data: () => ({
      showDeleteModal: false,
    }),
    computed: {
      activeOrder() {
        return this.$store.getters.activeOrder
      },
      orders() {
        if (!this.activeOrder || !this.activeOrder.orders) return []
        return this.activeOrder.orders.sort(function(a,b){
          return new Date(b.created_at_full) - new Date(a.created_at_full);
        });
      },
      title() {
        if(this.activeOrder && this.activeOrder.name)
          return this.activeOrder.name
        if(this.activeOrder && this.activeOrder.table)
          return this.activeOrder.table.name
        return ''
      },
    },
    methods: {
      handleDelete() {
        this.$store.dispatch('deleteThirdPartyInvoice', this.activeOrder.id)
          .then(() => {
            this.showDeleteModal = false
            this.$store.commit('setActiveOrder', null)
          })
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
