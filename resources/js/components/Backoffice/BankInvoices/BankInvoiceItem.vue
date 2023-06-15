<template>
  <tr>
    <td class="relative py-2 px-4 border-b border-gray-200">
      <div class="flex gap-x-6">
        <svg v-if="invoice.status === 0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="hidden h-6 w-5 flex-none text-gray-400 sm:block">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm.53 5.47a.75.75 0 00-1.06 0l-3 3a.75.75 0 101.06 1.06l1.72-1.72v5.69a.75.75 0 001.5 0v-5.69l1.72 1.72a.75.75 0 101.06-1.06l-3-3z" clip-rule="evenodd" />
        </svg>
        <svg v-if="invoice.status === 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="hidden h-6 w-5 flex-none text-gray-400 sm:block">
          <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
        </svg>
        <svg v-if="invoice.status === 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="hidden h-6 w-5 flex-none text-gray-400 sm:block">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
        </svg>


        <div class="flex-auto">
          <div class="flex items-start gap-x-3">
            <div class="text-sm font-medium leading-6 text-gray-900">{{ $filters.formatPrice(invoice.amount) }} RSD</div>
            <div v-if="invoice.status === 0" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-gray-600 bg-gray-50 ring-gray-500/10">Neplaćeno</div>
            <div v-if="invoice.status === 1" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20">Plaćeno</div>
            <div v-if="invoice.status === 2" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-red-700 bg-red-50 ring-red-600/10">Otkazano</div>
          </div>
          <div class="mt-1 text-xs leading-5 text-gray-400" :class="[invoice.status === 1 && 'text-green-600']">{{ $filters.formatDate(invoice.transaction_date) }}</div>
        </div>
      </div>
    </td>
    <td class="hidden py-2 pr-6 sm:table-cell px-4 border-b border-gray-200">
      <div class="text-sm leading-6 text-gray-900">{{ invoice?.client_account?.name }}</div>
      <div class="text-sm leading-6 text-gray-900">
        {{ invoice?.client_account?.bank_account }}
        <span class="mt-1 text-sm leading-5 text-gray-400">{{ invoice?.reference_number ? ` (${invoice.reference_number})` : '' }}</span>
      </div>

    </td>
    <td class="py-2 px-4 border-b border-gray-200">
      <div class="flex items-center justify-end gap-5">
        <div>
          <div class="mt-1 text-xs leading-5 text-gray-900">{{ invoice.created_at !== invoice.updated_at ? $filters.formatDate(invoice.updated_at) : '' }}</div>
          <div class="mt-1 text-xs leading-5 text-gray-400">#{{ invoice.id.split('-').at(-1) }}</div>
        </div>
        <div class="relative">
          <svg @click="toggleMenu" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-6 w-5 flex-none text-gray-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"></path>
          </svg>
          <div v-if="showMenu">
            <div class="fixed top-0 left-0 bg-black bg-opacity-[1%] w-full h-screen z-[100]" @click="toggleMenu" />
            <div class="bg-white shadow-sm absolute top-0 right-0 z-[101] w-64 rounded-md border border-gray-200">
              <div class="grid grid-cols-1">
                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="showPayModal = true; showMenu = false">
                  Plati
                </div>
                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="updateInvoiceStatus(2)">
                  Otkaži
                </div>
                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="updateInvoiceStatus(0)">
                  Vrati na neplaćeno
                </div>
              </div>
            </div>
          </div>
        </div>
        <BankInvoicePayModal v-if="showPayModal" @close="showPayModal = false" @updateInvoice="$emit('updateInvoiceStatus')" :invoice="invoice" />
      </div>
    </td>
  </tr>
</template>

<script>
  import BankInvoicePayModal from './BankInvoicePayModal.vue'

  export default {
    props: {
      invoice: {
        type: Object,
        required: true
      }
    },
    components: {
      BankInvoicePayModal,
    },
    data: () => ({
      showMenu: false,
      showPayModal: false,
    }),
    methods: {
      toggleMenu() {
        this.showMenu = !this.showMenu
      },
      togglePayModal() {
        this.showPayModal = !this.showPayModal
      },
      updateInvoiceStatus(status) {
        axios.put(`/api/bank-invoices/${this.invoice.id}`, {
            status
          })
          .then((response) => {
            this.$emit('updateInvoiceStatus')
            this.showMenu = false
          })
      }
    },
  }
</script>
