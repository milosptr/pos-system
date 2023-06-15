<template>
  <Modal :numpad="true">
    <EnterPin
      v-show="!validPin"
      @success="success" />
    <div
      class="flex h-full flex-col justify-between"
      v-show="validPin">
      <div class="">
        <div class="mb-6 text-center text-2xl font-semibold uppercase">Pregled prometa {{ today }}</div>
        <div
          v-if="hasActiveOrders"
          class="text-2xl font-medium text-red-500">
          Imate jos nenaplaćenih stolova:
          <div>
            {{ activeTables }}
          </div>
        </div>
        <div
          v-if="!hasActiveOrders"
          class="relative mt-10 flex flex-col items-center justify-center">
          <canvas
            :width="canvasWidth"
            :height="canvasHeight"
            id="transactionsGraph"></canvas>
          <div
            class="absolute left-0 top-0 flex w-full items-center justify-center text-center text-4xl font-bold"
            :style="`height: ${canvasHeight}px`">
            <div class="">
              {{ $filters.formatPrice(transactions.income) }}
            </div>
          </div>
        </div>
        <div
          v-if="!hasActiveOrders"
          class="mt-10 text-xl">
          <div class="flex items-center justify-between py-1 font-medium">
            <div>Gotovina</div>
            <div>{{ $filters.formatPrice(transactions.income) }} RSD</div>
          </div>
          <div class="flex items-center justify-between py-1 font-medium">
            <div>Reprezentacija</div>
            <div>{{ $filters.formatPrice(transactions.onthehouse) }} RSD</div>
          </div>
          <div class="flex items-center justify-between border-b border-gray-300 py-1 pb-3 font-medium">
            <div>Storno</div>
            <div>{{ $filters.formatPrice(transactions.refund) }} RSD</div>
          </div>
          <div class="flex items-center justify-between py-1 pt-3 font-bold">
            <div>Ukupno</div>
            <div>{{ $filters.formatPrice(transactions.total) }} RSD</div>
          </div>
        </div>
      </div>
      <div
        class="mt-8 flex justify-center"
        @click="$emit('close')">
        <div class="w-48 bg-red-600 px-6 py-3 text-center text-lg font-medium uppercase text-white">NAZAD</div>
      </div>
    </div>
  </Modal>
</template>

<script>
import EnterPin from '../EnterPin.vue'
import Modal from './Modal.vue'

export default {
  components: {
    Modal,
    EnterPin
  },
  data: () => ({
    canvasWidth: 700,
    canvasHeight: 276,
    maximum: 70000,
    transactions: {
      total: 0,
      storno: 0,
      neto: 0
    },
    validPin: false,
    period: 'day',
    options: {
      angle: -0.3, // The span of the gauge arc
      lineWidth: 0.08, // The line thickness
      radiusScale: 1.1, // Relative radius
      pointer: {
        length: 0, // // Relative to gauge radius
        strokeWidth: 0, // The thickness
        color: '#000000' // Fill color
      },
      limitMax: true, // If false, max value increases automatically if value > maxValue
      limitMin: true, // If true, the min value of the gauge will be fixed
      colorStart: '#e86423', // Colors
      colorStop: '#e86423', // just experiment with them
      strokeColor: '#EEEEEE', // to see which ones work best for you
      generateGradient: false,
      highDpiSupport: true
    },
    workingDay: new Date(),
    activeTables: []
  }),
  computed: {
    today() {
      return dayjs(this.workingDay).format('DD.MM.YYYY')
    },
    hasActiveOrders() {
      return !!this.activeTables.length
    },
    activeTables() {
      return this.activeTables.map((t) => t.name).join(', ')
    }
  },
  mounted() {
    axios.get('/api/working-day').then((res) => {
      this.workingDay = res.data[0]
    })
    axios.get('/api/active-orders').then((res) => {
      this.activeTables = res.data.data
    })
    axios.get('/api/invoices/daily-maximum').then((res) => {
      this.maximum = parseInt(res.data.total)
    })

    if (window.innerWidth < 1200) {
      this.canvasWidth = 370
      this.canvasHeight = 145
    }
  },
  methods: {
    success() {
      this.getTransactions()
    },
    getTransactions() {
      axios.get('/api/invoices/today-transactions').then((res) => {
        this.transactions = res.data
        this.validPin = true
        setTimeout(() => {
          let target = document.getElementById('transactionsGraph') // your canvas element
          let gauge = new Gauge(target).setOptions(this.options) // create sexy gauge!
          gauge.maxValue = parseInt(this.maximum) // set max gauge value
          gauge.setMinValue(0) // Prefer setter over gauge.minValue = 0
          gauge.animationSpeed = 32 // set animation speed (32 is default value)
          gauge.set(parseInt(this.transactions.income)) // set actual value
        }, 500)
      })
    }
  }
}
</script>

<style scoped></style>
