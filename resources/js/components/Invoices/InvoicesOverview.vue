<template>
  <div class="p-4 bg-gray-100">
    <div class="flex items-center gap-2 justify-end">
      <div class="text-2xl font-semibold uppercase">
        Pregled računa za {{ today }}
      </div>
      <router-link to="/"
        class="w-32 px-6 py-3 bg-red-600 text-center text-white text-lg uppercase font-medium ml-auto"
      >
        Nazad
      </router-link>
    </div>
    <div class="py-1 px-4 mt-5 font-semibold rounded-sm grid grid-cols-5 gap-3 text-sm">
      <div class="text-left">
        Vreme naplate
      </div>
      <div class="">
        Total
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
    </div>
    <div
      v-for="(invoice) in invoices"
      :key="invoice.id"
      class="bg-white mt-2 py-2 px-4 text-xl rounded-sm border  grid grid-cols-5 gap-3 items-center font-medium"
      :class="[activeInvoice && invoice.id === activeInvoice.id ? 'border-primary' : 'border-gray-300', invoice.status === 0 ? 'text-red-500' : '']"
      @click="selectInvoice(invoice.id)"
    >
      <div class="px-2">
        {{ getTime(invoice.created_at) }}
      </div>
      <div :class="{'line-through': invoice.status === 0}">
        {{ $filters.formatPrice(invoice.total) }} RSD
      </div>
      <div class="text-center">
        {{ invoice?.table?.name }}
      </div>
      <div class="text-center">
        <div>{{ invoice.status ? 'Naplacen' : 'Storniran' }}</div>
        <div class="text-sm whitespace-nowrap">{{ invoice.refund_reason }}</div>
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
      workingDay: new Date(),
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
      axios.get('/api/working-day')
        .then((res) => {
          this.workingDay = res.data[0]
        })
      this.$store.dispatch('getInvoices')
    },
    methods: {
      selectInvoice(id) {
        this.$store.commit('setActiveInvoice', id)
      },
      getTime(date) {
        const pattern = /(\d{2})\.(\d{2})\.(\d{4})/;
        return dayjs(date.replace(pattern,'$3-$2-$1')).format('HH:mm:ss')
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
