<template>
  <Modal :numpad="true">
    <div class="h-full flex flex-col justify-between">
      <div class="">
        <div class="text-center text-2xl font-semibold mb-6 uppercase">{{ item.name }}</div>
        <div class="grid grid-cols-2 gap-5">
          <div>
            <label for="pricingDate" class="block text-sm font-medium text-gray-700">Datum</label>
            <div class="mt-1">
              <litepie-datepicker
                i18n="sr"
                as-single
                :formatter="formatter"
                :auto-apply="false"
                readonly
                aria-readonly="true"
                v-model="pricing.date"
              />
            </div>
          </div>
          <div>
            <label for="retail_price" class="block text-sm font-medium text-gray-700">Prodajna cena</label>
            <div class="mt-1">
              <input v-model="pricing.retail_price" type="number" name="retail_price" min="0" id="retail_price" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" :placeholder="[lastPricing ? lastPricing.retail_price : '0.00']">
            </div>
          </div>
          <div>
            <label for="purchase_price" class="block text-sm font-medium text-gray-700">Nabavna cena</label>
            <div class="mt-1">
              <input v-model="pricing.purchase_price" type="number" name="purchase_price" min="0" id="purchase_price" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" :placeholder="[lastPricing ? lastPricing.purchase_price : '0.00']">
            </div>
          </div>
          <div>
            <label for="normativ" class="block text-sm font-medium text-gray-700">Normativ</label>
            <div class="mt-1">
              <input v-model="pricing.norm" type="number" name="normativ" min="0" id="normativ" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" :placeholder="[lastPricing ? lastPricing.norm : '0.00']">
            </div>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-5">
        <button type="button" class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="$emit('close')">
          Cancel
        </button>
        <button type="button" class="relative inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="savePrices">
          Save
        </button>
      </div>
    </div>
  </Modal>
</template>

<script>
  import Modal from './Modal.vue'

  export default {
    props: {
      item: {
        type: Object,
         default: () => {}
      },
      price: {
        type: Number,
        default: () => 0
      }
    },
    data: () =>({
      formatter: {
        date: 'YYYY-MM-DD',
        month: 'MMM'
      },
      pricing: {
        date: dayjs().format('YYYY-MM-DD')
      }
    }),
    components: {
      Modal,
    },
    computed: {
      lastPricing() {
        return this.item.pricing[0]
      }
    },
    mounted() {
      this.pricing.inventory_id = this.item.id
    },
    methods: {
      savePrices() {
        if(!this.pricing.retail_price) this.pricing.retail_price = this.lastPricing.retail_price
        if(!this.pricing.purchase_price) this.pricing.purchase_price = this.lastPricing.purchase_price
        if(!this.pricing.norm) this.pricing.norm = this.lastPricing.norm
        this.pricing.date = dayjs(this.pricing.date + ' ' + dayjs().format('HH:mm:ss')).format('YYYY-MM-DD HH:mm:ss')
        this.$emit('save', this.pricing)
      }
    }
  }
</script>
