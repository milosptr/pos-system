<template>
  <TransitionRoot as="template" :show="!!activeInventoryPricing">
    <Dialog as="div" class="relative z-10" @close="$store.commit('setActiveInventoryPricing', null)">
      <div class="fixed inset-0" />

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-xl">
                <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                  <div class="px-4 sm:px-6">
                    <div class="flex items-start justify-between">
                      <DialogTitle class="text-2xl font-bold text-gray-900"> {{ title }} </DialogTitle>
                      <div class="ml-3 flex h-7 items-center">
                        <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="$store.commit('setActiveInventoryPricing', null)">
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="relative mt-6 flex-1 px-4 sm:px-6">
                    <div class="grid grid-cols-2 gap-5 mb-10 sm:hidden">
                      <div
                        class="px-4 py-2 rounded-md bg-gray-300 text-white text-center"
                        :class="{'bg-indigo-500': activeTab === 2}"
                        @click="activeTab = 2"
                      >
                        Norm History
                      </div>
                      <div
                        class="px-4 py-2 rounded-md bg-gray-300 text-white text-center"
                        :class="{'bg-indigo-500': activeTab === 1}"
                        @click="activeTab = 1"
                      >
                        Price History
                      </div>
                    </div>
                    <table v-if="!!activeInventoryPricing" class="min-w-full border-separate" style="border-spacing: 0">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-2 px-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Datum</th>
                          <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter" :class="{'hidden sm:table-cell': activeTab === 2}">Prodajna</th>
                          <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter" :class="{'hidden': activeTab === 1}">Nabavna</th>
                          <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter whitespace-nowrap" :class="{'hidden': activeTab === 1}">No</th>
                          <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter whitespace-nowrap" :class="{'hidden': activeTab === 1}">Razlika</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white">
                        <tr
                          v-for="(item, idx) in pricing"
                          :key="idx"
                          class="hover:bg-orange-50 cursor-pointer"
                        >
                          <td class="whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-600" :class="[idx !== pricing.length - 1 ? 'border-b border-gray-200' : '']">{{ formatDate(item.date) }}</td>
                          <td class="whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-500" :class="[idx !== pricing.length - 1 ? 'border-b border-gray-200' : '', {'hidden sm:table-cell': activeTab === 2}]">{{ $filters.formatPrice(item.retail_price) }} RSD</td>
                          <td class="whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-500" :class="[idx !== pricing.length - 1 ? 'border-b border-gray-200' : '', {'hidden': activeTab === 1}]">{{ $filters.formatPrice(item.purchase_price) }} RSD</td>
                          <td class="whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-500" :class="[idx !== pricing.length - 1 ? 'border-b border-gray-200' : '', {'hidden': activeTab === 1}]">{{ item.norm }}</td>
                          <td class="whitespace-nowrap py-2 px-3 text-sm font-medium text-gray-500" :class="[idx !== pricing.length - 1 ? 'border-b border-gray-200' : '', {'hidden': activeTab === 1}]">{{ calculateDiff(item) }}</td>
                        </tr>
                      </tbody>
                    </table>
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

  export default {
    components: {
      Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, XIcon,
    },
    data: () => ({
      activeTab: 2,
    }),
    computed: {
      activeInventoryPricing() {
        return this.$store.getters.activeInventoryPricing
      },
      pricing() {
        return this.activeInventoryPricing.pricing
      },
      title() {
        if(this.activeInventoryPricing && this.activeInventoryPricing.name)
          return this.activeInventoryPricing.name
        return ''
      },
    },
    mounted() {},
    methods: {
      formatDate(date) {
        return dayjs(date).format('YYYY-MM-DD')
      },
      calculateDiff(item) {
        const diff = item.retail_price / (item.norm * item.purchase_price)
        return diff.toFixed(2)
      }
    },
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
