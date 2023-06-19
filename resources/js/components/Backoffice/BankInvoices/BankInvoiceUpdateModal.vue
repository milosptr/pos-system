<template>
  <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-[100]">
    <div class="absolute left-0 top-0 w-full h-screen bg-black bg-opacity-50" @click="$emit('close')"></div>
    <div class="bg-white w-full h-screen md:h-auto md:w-1/2 rounded-md px-4 py-6 z-[101]">
      <div class="font-semibold">
        {{ invoice.client_account?.name }}
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 items-end gap-5">
        <div>
          <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Poziv na broj</label>
          <div class="mt-2">
            <input type="text" v-model="transaction.reference_number" name="reference_number" id="reference_number" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
        </div>
        <div>
          <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Iznos</label>
          <div class="mt-2">
            <input type="text" v-model="transaction.amount" name="amount" id="amount" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
        </div>
        <div>
          <label for="payment_deadline" class="block text-sm font-medium leading-6 text-gray-900">Datum valute</label>
          <div class="mt-2">
            <input type="date" v-model="transaction.payment_deadline" name="payment_deadline" id="payment_deadline" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
        </div>
        <div>
          <label for="transaction_date" class="block text-sm font-medium leading-6 text-gray-900">Datum prometa</label>
          <div class="mt-2">
            <input type="date" v-model="transaction.transaction_date" name="transaction_date" id="transaction_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
        </div>
        <div @click="$emit('close')" class="cursor-pointer mt-6 md:mt-0 text-center px-4 py-2 border border-gray-400 shadow-sm text-sm font-medium rounded-md text-gray-900 bg-white hover:bg-gray-400 focus:outline-none">Zatvori</div>
        <div class="hidden md:block"></div>
        <div class="hidden md:block"></div>
        <div @click="updateInvoice" class="cursor-pointer text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Izmeni</div>
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
    data: () => ({
      transaction: {
        currency_date: new Date().toISOString().slice(0,10),
        transaction_date: new Date().toISOString().slice(0,10),
        amount: null
      }
    }),
    mounted() {
      this.$nextTick(() => {
        this.transaction.amount = this.invoice.amount
        this.transaction.payment_deadline = this.invoice.payment_deadline.slice(0,10)
        this.transaction.transaction_date = this.invoice.transaction_date.slice(0,10)
        this.transaction.reference_number = this.invoice.reference_number
      })
    },
    methods: {
      updateInvoice() {
        const amount = this.transaction.amount.toString()
        this.transaction.amount = parseFloat(amount.replace(',', '.'))

        axios.put(`/api/bank-invoices/${this.invoice.id}`, this.transaction)
          .then(() => {
            this.$emit('updateInvoice')
          })
      }
    }
  }
</script>
