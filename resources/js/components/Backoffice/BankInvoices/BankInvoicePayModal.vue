<template>
  <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-[100]">
    <div class="absolute left-0 top-0 w-full h-screen bg-black bg-opacity-50" @click="$emit('close')"></div>
    <div class="bg-white w-full h-screen md:h-auto md:w-1/2 rounded-md px-4 py-6 z-[101]">
      <div class="">
        <h3 class="text-2xl font-semibold leading-6 text-gray-900" id="modal-title">Potvrda plaćanja</h3>
        <div class="mt-6">
          <div class="flex items-center text-gray-700"><div class="w-[150px]">Za:</div> <div class="font-semibold">{{ invoice.client_account?.name }}</div></div>
          <div class="flex items-center text-gray-700"><div class="w-[150px]">Broj računa:</div> <div class="font-semibold">{{ invoice.client_account?.bank_account }}</div></div>
          <div class="flex items-center text-gray-700"><div class="w-[150px]">Iznos:</div> <div class="font-semibold">{{ $filters.formatPrice(invoice.amount, true) }} rsd</div></div>
          <div class="flex items-center text-gray-700"><div class="w-[150px]">Datum valute:</div> <div class="font-semibold">{{ $filters.formatDate(invoice.payment_deadline) }}</div></div>
          <div class="flex items-center text-gray-700"><div class="w-[150px]">Datum prometa:</div> <div class="font-semibold">{{ $filters.formatDate(invoice.transaction_date) }}</div></div>
        </div>
      </div>
      <div class="mt-5 sm:mt-10 flex flex-col sm:flex-row justify-between gap-8">
        <div class="sm:w-1/2 text-center relative px-4 py-2 border border-gray-400 shadow-sm text-sm font-medium rounded-md text-gray-500 hover:text-white hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 cursor-pointer" @click="$emit('close')">Odustani</div>
        <div class="sm:w-1/2 text-center relative px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer" @click="payInvoice">Plati</div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      invoice: {
        type: Object,
        required: true,
      },
    },
    methods: {
      payInvoice() {
        axios.put(`/api/bank-invoices/${this.invoice.id}`, {status: 1})
          .then(() => {
            this.$emit('updateInvoice')
          })
      }
    }
  }
</script>
