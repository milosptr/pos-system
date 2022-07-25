<template>
  <div class="p-4 bg-gray-100">
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
      <div class="text-center">
        Broj stola
      </div>
      <div class="text-center">
        Status
      </div>
      <div class="text-center">
        Konobar
      </div>
      <div class="text-center">
        Vreme naplate
      </div>
    </div>
    <div
      v-for="(invoice, index) in invoices"
      :key="invoice.id"
      class="bg-white mt-2 py-3 px-4 text-xl rounded-sm border  grid grid-cols-5 gap-3 items-center font-medium"
      :class="[activeInvoice && invoice.id === activeInvoice.id ? 'border-primary' : 'border-gray-300', invoice.status === 0 ? 'text-red-500' : '']"
      @click="selectInvoice(invoice.id)"
    >
      <div class="flex items-center">
        <div class="w-10">
          {{ index + 1 }}
        </div>
        <div :class="{'line-through': invoice.status === 0}">
          {{ $filters.formatPrice(invoice.total) }} RSD
        </div>
      </div>
      <div class="text-center">
        {{ invoice?.table?.name }}
      </div>
      <div class="text-center">
        <div>{{ invoice.status ? 'Naplacen' : 'Storniran' }}</div>
        <div class="text-sm">{{ invoice.refund_reason }}</div>
      </div>
      <div class="text-center">
        {{ invoice.user.name }}
      </div>
      <div class="text-center text-xl px-2">
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

<style scoped>
  @media (max-width: 1024px) {
    .text-xl,
    .text-xl * {
      font-size: 14px;
    }
  }
</style>
