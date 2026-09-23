<template>
  <tr class="cursor-pointer hover:bg-gray-50" @click="toggleDetails">
    <td colspan="3" class="p-0">
    <div class="grid grid-cols-2 sm:grid-cols-3">
    <div class="relative col-span-2 sm:col-span-1 w-full py-2 px-4 sm:border-b border-gray-200">
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
            <div class="text-sm font-medium leading-6 cursor-pointer" :class="copied === 'amount' ? 'text-green-600' : 'text-gray-900'" title="Klikni da kopiraš iznos" @click.stop="clickToCopy(invoice.amount, 'amount')">{{ $filters.formatPrice(invoice.amount, true) }} RSD</div>
            <div v-if="copied === 'amount'" class="text-xs font-medium leading-6 text-green-600">Kopirano</div>
            <div v-if="invoice.status === 0" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-gray-600 bg-gray-50 ring-gray-500/10">Neplaćeno</div>
            <div v-if="invoice.status === 1" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-green-700 bg-green-50 ring-green-600/20">Plaćeno</div>
            <div v-if="invoice.status === 2" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset text-red-700 bg-red-50 ring-red-600/10">Otkazano</div>
          </div>
          <div class="mt-1 text-xs leading-5 text-gray-400" :class="[invoice.status === 1 && 'text-green-600']">{{ $filters.formatDate(invoice.payment_deadline) }}</div>
        </div>
      </div>
    </div>
    <div class="py-2 pr-6 px-4 border-b border-gray-200">
      <div class="text-sm leading-6 text-gray-900">{{ invoice?.client_account?.name }}</div>
      <div v-if="invoice?.reference_number" class="text-sm leading-6 text-gray-900">
        <span class="mt-1 text-sm leading-5 cursor-pointer" :class="copied === 'reference' ? 'text-green-600' : 'text-gray-400'" title="Klikni da kopiraš poziv na broj" @click.stop="clickToCopy(invoice.reference_number, 'reference')">
          <span v-if="paymentModel" class="font-semibold" :class="copied === 'reference' ? 'text-green-600' : 'text-gray-600'">{{ paymentModel }}</span>
          {{ invoice.reference_number }}
        </span>
        <span v-if="copied === 'reference'" class="ml-2 text-xs font-medium text-green-600">Kopirano</span>
      </div>

    </div>
    <div class="py-2 px-4 border-b border-gray-200">
      <div class="flex items-center justify-end gap-5">
        <div class="md:w-32">
          <div class="mt-1 text-xs leading-5 text-gray-900 text-left" v-if="invoice.created_at !== invoice.transaction_date">Datum prometa</div>
          <div class="mt-1 text-xs leading-5 text-gray-400 text-left">{{ invoice.created_at !== invoice.transaction_date ? $filters.formatDate(invoice.transaction_date) : '' }}</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 flex-none text-gray-400 transition-transform" :class="[expanded && 'rotate-180']">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
        <div class="relative">
          <svg @click.stop="toggleMenu" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-6 w-5 flex-none text-gray-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"></path>
          </svg>
          <div v-if="showMenu" @click.stop>
            <div class="fixed top-0 left-0 bg-black bg-opacity-[1%] w-full h-screen z-[100]" @click="toggleMenu" />
            <div class="bg-white shadow-sm absolute top-0 right-0 z-[101] w-64 rounded-md border border-gray-200">
              <div class="grid grid-cols-1">
                <div class="block px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="showPayModal = true; showMenu = false">
                  Plati
                </div>
                <div class="block px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="showDeleteInvoiceModal = true; showMenu = false">
                  Izbriši
                </div>
                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="updateInvoiceStatus(0)">
                  Vrati na neplaćeno
                </div>
                <div class="block text-indigo-500 border-t px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 cursor-pointer" @click="showUpdateModal = true; showMenu = false">
                  Izmeni
                </div>
              </div>
            </div>
          </div>
        </div>
        <BankInvoicePayModal v-if="showPayModal" @close="showPayModal = false" @updateInvoice="$emit('updateInvoiceStatus')" :invoice="invoice" />
        <BankInvoiceDeleteModal v-if="showDeleteInvoiceModal" @close="showDeleteInvoiceModal = false" @updateInvoice="showDeleteInvoiceModal = false; $emit('updateInvoiceStatus')" :invoice="invoice" />
        <BankInvoiceUpdateModal v-if="showUpdateModal" @close="showUpdateModal = false" @updateInvoice="showUpdateModal = false; $emit('updateInvoiceStatus')" :invoice="invoice" />
      </div>
    </div>
    </div>
    </td>
  </tr>
  <tr v-if="expanded" class="border-b border-gray-200 bg-gray-50">
    <td colspan="3" class="px-4 py-4">
      <dl class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-3">
        <div v-for="detail in details" :key="detail.label">
          <dt class="text-xs leading-5 text-gray-400">{{ detail.label }}</dt>
          <dd class="text-sm leading-5 text-gray-900">{{ detail.value }}</dd>
        </div>
      </dl>

      <div v-if="loadingItems" class="mt-4 text-sm text-gray-400">Učitavanje stavki...</div>
      <div v-else-if="itemsError" class="mt-4 text-sm text-red-600">
        Greška pri učitavanju stavki.
        <span class="underline cursor-pointer" @click.stop="fetchItems">Pokušaj ponovo</span>
      </div>
      <div v-else-if="!items.length" class="mt-4 text-sm text-gray-400">Nema stavki za ovu fakturu.</div>
      <div v-else class="mt-4 overflow-x-auto">
      <table class="w-full text-left text-xs whitespace-nowrap">
        <thead>
          <tr class="text-gray-400 border-b border-gray-200">
            <th class="font-medium py-1 pr-3">Rb</th>
            <th class="font-medium py-1 pr-3">Šifra</th>
            <th class="font-medium py-1 pr-3">Naziv</th>
            <th class="font-medium py-1 pr-3 text-right">Količina</th>
            <th class="font-medium py-1 pr-3">JM</th>
            <th class="font-medium py-1 pr-3 text-right">Cena</th>
            <th class="font-medium py-1 pr-3 text-right">Cena sa PDV</th>
            <th class="font-medium py-1 pr-3 text-right">Osnovica</th>
            <th class="font-medium py-1 text-right">PDV</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="text-gray-900 border-b border-gray-100 last:border-0">
            <td class="py-1 pr-3 text-gray-400">{{ item.position }}</td>
            <td class="py-1 pr-3 text-gray-400">{{ item.sku }}</td>
            <td class="py-1 pr-3">{{ item.name }}</td>
            <td class="py-1 pr-3 text-right">{{ item.quantity }}</td>
            <td class="py-1 pr-3 text-gray-400">{{ item.unit }}</td>
            <td class="py-1 pr-3 text-right">{{ $filters.formatPrice(item.unit_price, true) }}</td>
            <td class="py-1 pr-3 text-right">{{ item.unit_price_gross ? $filters.formatPrice(item.unit_price_gross, true) : '-' }}</td>
            <td class="py-1 pr-3 text-right">{{ $filters.formatPrice(item.net_amount, true) }}</td>
            <td class="py-1 text-right text-gray-400">{{ item.vat_rate }}%</td>
          </tr>
        </tbody>
      </table>
      </div>
    </td>
  </tr>
</template>

<script>
  import BankInvoiceDeleteModal from './BankInvoiceDeleteModal.vue'
  import BankInvoicePayModal from './BankInvoicePayModal.vue'
import BankInvoiceUpdateModal from './BankInvoiceUpdateModal.vue'

  const COPIED_FEEDBACK_MS = 1500

  export default {
    props: {
      invoice: {
        type: Object,
        required: true
      },
    },
    components: {
    BankInvoicePayModal,
    BankInvoiceUpdateModal,
    BankInvoiceDeleteModal,
},
    data: () => ({
      showMenu: false,
      showPayModal: false,
      showUpdateModal: false,
      showDeleteInvoiceModal: false,
      expanded: false,
      loadingItems: false,
      itemsLoaded: false,
      itemsError: false,
      items: [],
      copied: null,
    }),
    beforeUnmount() {
      clearTimeout(this.copiedTimeout)
    },
    computed: {
      // Model 00 means the slip carries no model at all, so printing it would
      // be noise next to the poziv na broj.
      paymentModel() {
        const model = (this.invoice.payment_model || '').trim()
        return Number(model) > 0 ? model : null
      },
      details() {
        return [
          { label: 'Broj računa', value: this.invoice.invoice_number },
          { label: 'Model', value: this.paymentModel },
          { label: 'Poziv na broj', value: this.invoice.reference_number },
          { label: 'Datum izdavanja', value: this.invoice.issue_date ? this.$filters.formatDate(this.invoice.issue_date) : null },
          { label: 'Datum prometa', value: this.invoice.transaction_date ? this.$filters.formatDate(this.invoice.transaction_date) : null },
          { label: 'Datum valute', value: this.invoice.payment_deadline ? this.$filters.formatDate(this.invoice.payment_deadline) : null },
          { label: 'PIB', value: this.invoice.supplier_pib || this.invoice?.client_account?.pib },
          { label: 'Tekući račun', value: this.invoice.supplier_bank_account || this.invoice?.client_account?.bank_account },
          { label: 'SEF ID', value: this.invoice.sef_id },
          { label: 'Plaćeno', value: this.invoice.processed_at ? this.$filters.formatDate(this.invoice.processed_at) : null },
        ].filter((detail) => detail.value)
      },
    },
    methods: {
      toggleDetails() {
        this.expanded = !this.expanded
        if (this.expanded && !this.itemsLoaded) {
          this.fetchItems()
        }
      },
      fetchItems() {
        this.loadingItems = true
        this.itemsError = false
        axios.get(`/api/bank-invoices/${this.invoice.id}/items`)
          .then((response) => {
            this.items = response.data
            this.itemsLoaded = true
          })
          .catch((error) => {
            // Never let a failed request read as "this invoice has no items".
            this.itemsError = true
            console.error('Failed to load invoice items: ', error)
          })
          .finally(() => {
            this.loadingItems = false
          })
      },
      toggleMenu() {
        this.showMenu = !this.showMenu
      },
      togglePayModal() {
        this.showPayModal = !this.showPayModal
      },
      clickToCopy(value, field) {
        this.$copyText(value.toString())
          .then(() => {
            this.copied = field
            clearTimeout(this.copiedTimeout)
            this.copiedTimeout = setTimeout(() => {
              this.copied = null
            }, COPIED_FEEDBACK_MS)
          })
          .catch((error) => {
            console.error('Failed to copy: ', error)
          })
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
