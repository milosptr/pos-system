<template>
  <Modal :numpad="true">
    <EnterPin v-show="!validPin" @success="success" />
    <div v-show="validPin">
      <div class="flex flex-col items-center justify-center h-full mt-10 relative">
        <canvas width="700" height="276" id="transactionsGraph"></canvas>
        <div class="absolute left-0 top-0 text-center w-full font-bold text-4xl flex items-center justify-center" style="height: 276px">
          <div class="">
            {{ $filters.formatPrice(transactions.neto) }}
          </div>
        </div>
      </div>
      <div class="mt-10 text-2xl">
        <div class="flex justify-between items-center py-2 font-medium">
          <div>Gotovina</div>
          <div>{{ $filters.formatPrice(transactions.total) }} RSD</div>
        </div>
        <div class="flex justify-between items-center py-2 pb-3 border-b border-gray-300 font-medium">
          <div>Storno</div>
          <div>{{ $filters.formatPrice(transactions.storno) }} RSD</div>
        </div>
        <div class="flex justify-between items-center py-2 pt-3 font-bold">
          <div>Ukupno</div>
          <div>{{ $filters.formatPrice(transactions.neto) }} RSD</div>
        </div>
      </div>
      <div class="flex justify-center mt-10" @click="$emit('close')">
        <div class="w-48 px-6 py-3 bg-red-600 text-center text-white text-lg uppercase font-medium">
          NAZAD
        </div>
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
      EnterPin,
    },
    data: () => ({
      transactions: {
        maximum: 70000,
        total: 0,
        storno: 0,
        neto: 0,
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
        limitMax: true,     // If false, max value increases automatically if value > maxValue
        limitMin: true,     // If true, the min value of the gauge will be fixed
        colorStart: '#e86423',   // Colors
        colorStop: '#e86423',    // just experiment with them
        strokeColor: '#EEEEEE',  // to see which ones work best for you
        generateGradient: false,
        highDpiSupport: true,
      }
    }),
    mounted() {

    },
    methods: {
      success() {
        this.getTransactions()
      },
      getTransactions() {
        axios.get('/api/invoices/today-transactions')
          .then((res) => {
            this.transactions = res.data;
            this.validPin = true
            setTimeout(() => {
              let target = document.getElementById('transactionsGraph'); // your canvas element
              let gauge = new Gauge(target).setOptions(this.options); // create sexy gauge!
              gauge.maxValue = parseInt(this.transactions.maximum); // set max gauge value
              gauge.setMinValue(0);  // Prefer setter over gauge.minValue = 0
              gauge.animationSpeed = 32; // set animation speed (32 is default value)
              gauge.set(parseInt(this.transactions.neto)); // set actual value
            }, 500);
          })
      }
    }
  }
</script>
