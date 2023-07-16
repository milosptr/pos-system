<template>
  <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-[100]">
    <div class="absolute left-0 top-0 w-full h-screen bg-black bg-opacity-50" @click="$emit('close')"></div>
    <div class="bg-white md:w-1/2 rounded-md px-4 py-6 z-[101]">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-3">
          <div v-if="!addNewClient">
            <div class="flex items-end gap-3">
              <div class="w-full">
                <label for="clients" class="block text-sm font-medium leading-6 text-gray-900">Dobavljači</label>
                <select id="clients" v-model="invoice.client_account" name="clients" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                  <option v-for="client in clientsSorted" :key="client.id" :value="client.id">{{client.name}}</option>
                </select>
              </div>
              <div @click="addNewClient = true" class="cursor-pointer">
                <svg fill="none" stroke="currentColor" class="w-8 h-8 text-gray-500 mb-1" stroke-width="1" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
              </div>
            </div>
          </div>
          <div v-if="addNewClient">
            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Ime</label>
            <div class="mt-2">
              <input type="text" v-model="newClient.name" name="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
          </div>
          <div v-if="addNewClient" class="text-sm font-semibold underline text-red-400 cursor-pointer" @click="addNewClient = false">Otkaži</div>
        </div>
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label for="reference_number" class="block text-sm font-medium leading-6 text-gray-900">Poziv na broj</label>
            <div class="mt-2">
              <input type="text" v-model="invoice.reference_number" name="reference_number" id="reference_number" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
          </div>
          <div>
            <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Iznos</label>
            <div class="mt-2">
              <input type="text" v-model="invoice.amount" name="amount" id="amount" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
          </div>
          <div>
            <label for="transaction_date" class="block text-sm font-medium leading-6 text-gray-900">Datum prometa</label>
            <div class="mt-2">
              <input type="date" v-model="invoice.transaction_date" name="transaction_date" id="transaction_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
          </div>
          <div>
            <label for="payment_deadline" class="block text-sm font-medium leading-6 text-gray-900">Datum valute</label>
            <div class="mt-2">
              <input type="date" v-model="invoice.payment_deadline" name="payment_deadline" id="payment_deadline" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
          </div>
          <div class="mt-4 w-full px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-center" @click="addInvoice">Dodaj</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

  export default {
    data: () => ({
      clients: [],
      invoice: {
        reference_number: null,
        payment_deadline: null,
        transaction_date: null,
        amount: null,
        client_account: null,
      },
      addNewClient: false,
      newClient: {
        name: null,
        bank_account: null,
      }
    }),
    computed: {
      clientsSorted() {
        return this.clients.sort((a, b) => a.name.localeCompare(b.name))
      },
    },
    mounted() {
      this.$nextTick(() => {
        this.invoice.transaction_date = new Date().toISOString().slice(0, 10)
      })
      axios.get('/api/bank-accounts')
        .then((response) => {
          this.clients = response.data
        })
    },
    methods: {
      validate() {
        if(this.invoice.transaction_date && this.invoice.payment_deadline && this.invoice.amount) {
          if(this.addNewClient) {
            if(this.newClient.name) {
              return true
            } else {
              return 'Niste uneli sve podatke za novog dobavljača'
            }
          }
          if(this.invoice.client_account)
            return true
          return 'Niste uneli dobavljača'
        } else {
          return 'Niste uneli sve podatke za fakturu'
        }
      },
      addInvoice() {
        if(this.validate() === true) {
          const amount = this.invoice.amount.toString()
          this.invoice.amount = amount.replace('.', '').replace(',', '.')
          if(this.addNewClient) {
            axios.post('/api/bank-accounts', this.newClient)
              .then((response) => {
                this.invoice.client_account = response.data.id
                axios.post('/api/bank-invoices', this.invoice)
                  .then((response) => {
                    this.$emit('addInvoice', response.data)
                  })
              })
          } else {
            axios.post('/api/bank-invoices', this.invoice)
              .then((response) => {
                this.$emit('addInvoice', response.data)
              })
          }
        } else {
          alert(this.validate())
        }
      },
    }
  }
</script>
