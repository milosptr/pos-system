<template>
  <div class="bg-gray-100 p-4">
    <div class="flex items-center justify-end gap-2">
      <div class="text-2xl font-semibold uppercase">Pregled računa za {{ today }}</div>
      <router-link
        to="/"
        class="ml-auto w-32 bg-red-600 px-6 py-3 text-center text-lg font-medium uppercase text-white">
        Nazad
      </router-link>
    </div>
    <div class="mt-5 grid grid-cols-5 gap-3 rounded-sm px-4 py-1 text-sm font-semibold">
      <div class="text-left">Vreme naplate</div>
      <div class="">Total</div>
      <div class="text-center">Broj stola</div>
      <div class="text-center">Status</div>
      <div class="text-center">Konobar</div>
    </div>
    <div
      v-for="invoice in invoices"
      :key="invoice.id"
      class="mt-2 grid grid-cols-5 items-center gap-3 rounded-sm border bg-white px-4 py-2 text-xl font-medium"
      :class="[
        activeInvoice && invoice.id === activeInvoice.id ? 'border-primary' : 'border-gray-300',
        invoice.status === 0 ? 'text-red-500' : '',
        invoice.status === 2 ? 'text-indigo-500' : ''
      ]"
      @click="selectInvoice(invoice.id)">
      <div class="px-2">
        {{ getTime(invoice.created_at) }}
      </div>
      <div :class="{ 'line-through': invoice.status === 0 }">{{ $filters.formatPrice(invoice.total) }} RSD</div>
      <div class="text-center">
        {{ invoice?.table?.name }}
      </div>
      <div class="text-center">
        <div>{{ statusText(invoice.status) }}</div>
        <div class="whitespace-nowrap text-sm">{{ invoice.refund_reason }}</div>
      </div>
      <div class="text-center">
        {{ invoice.user.name }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    workingDay: new Date()
  }),
  computed: {
    invoices() {
      return this.$store.getters.invoices
    },
    activeInvoice() {
      return this.$store.getters.activeInvoice
    },
    today() {
      return dayjs(this.workingDay).format('DD.MM.YYYY')
    }
  },
  mounted() {
    axios.get('/api/working-day').then((res) => {
      this.workingDay = res.data[0]
    })
    this.$store.dispatch('getInvoices')
  },
  methods: {
    statusText(status) {
      if (status === 0) return 'Refundiran'
      if (status === 2) return 'Reprezentacija'
      return 'Naplaćen'
    },
    selectInvoice(id) {
      this.$store.commit('setActiveInvoice', id)
    },
    getTime(date) {
      const pattern = /(\d{2})\.(\d{2})\.(\d{4})/
      return dayjs(date.replace(pattern, '$3-$2-$1')).format('HH:mm:ss')
    }
  }
}
</script>

<style scoped>
@media (max-width: 1024px) {
  .text-xl,
  .text-xl * {
    font-size: 16px;
  }

  .text-xl .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
}
</style>
