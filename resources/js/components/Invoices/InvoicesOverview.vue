<template>
  <div class="p-4">
    <div class="flex items-center gap-2 justify-end">
      <router-link to="/"
        class="w-32 px-6 py-3 bg-red-600 text-center text-white text-lg uppercase font-medium ml-auto"
      >
        Nazad
      </router-link>
    </div>
    <div class="py-1 px-4 mt-5 font-semibold rounded-sm grid grid-cols-5 gap-3">
      <div class="flex items-center">
        <div class="w-10">
          No.
        </div>
        <div class="">
          Total
        </div>
      </div>
      <div class="">
        Broj artikla
      </div>
      <div class="">
        Status
      </div>
      <div class="">
        Konobar
      </div>
      <div class="">
        Vreme naplate
      </div>
    </div>
    <div
      v-for="(invoice, index) in invoices"
      :key="invoice.id"
      class="bg-white mt-2 py-3 px-4 text-lg rounded-sm border  grid grid-cols-5 gap-3 items-center font-medium"
      :class="[activeInvoice && invoice.id === activeInvoice.id ? 'border-primary' : 'border-gray-300']"
      @click="selectInvoice(invoice.id)"
    >
      <div class="flex items-center">
        <div class="w-10">
          {{ index + 1 }}
        </div>
        <div class="">
          {{ invoice.total }} RSD
        </div>
      </div>
      <div class="">
        {{ invoice.order.length }} artikala
      </div>
      <div class="">
        {{ invoice.status ? 'Naplacen' : 'Refundiran' }}
      </div>
      <div class="">
        Srdjan
      </div>
      <div class="">
        {{ invoice.created_at }}
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      invoices() {
        return this.$store.getters.invoices
      },
      activeInvoice() {
        return this.$store.getters.activeInvoice
      }
    },
    mounted() {
      this.$store.dispatch('getInvoices')
    },
    methods: {
      selectInvoice(id) {
        this.$store.commit('setActiveInvoice', id)
      }
    }
  }
</script>
