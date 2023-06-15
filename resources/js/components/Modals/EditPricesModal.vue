<template>
  <Modal :numpad="true">
    <div class="flex h-full flex-col justify-between">
      <div class="">
        <div class="mb-6 text-center text-2xl font-semibold uppercase">{{ item.name }}</div>
        <div class="gird-cols-1 grid gap-5 sm:grid-cols-2">
          <div>
            <label
              for="pricingDate"
              class="block text-sm font-medium text-gray-700"
              >Datum</label
            >
            <div class="mt-1">
              <litepie-datepicker
                i18n="sr"
                as-single
                :formatter="formatter"
                :auto-apply="false"
                readonly
                aria-readonly="true"
                v-model="pricing.date" />
            </div>
          </div>
          <div>
            <label
              for="retail_price"
              class="block text-sm font-medium text-gray-700"
              >Prodajna cena</label
            >
            <div class="mt-1">
              <input
                v-model="pricing.retail_price"
                type="number"
                name="retail_price"
                min="0"
                id="retail_price"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :placeholder="[lastPricing ? lastPricing.retail_price : '0.00']" />
            </div>
          </div>
          <div>
            <label
              for="purchase_price"
              class="block text-sm font-medium text-gray-700"
              >Nabavna cena</label
            >
            <div class="mt-1">
              <input
                v-model="pricing.purchase_price"
                type="number"
                name="purchase_price"
                min="0"
                id="purchase_price"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :placeholder="[lastPricing ? lastPricing.purchase_price : '0.00']" />
            </div>
          </div>
          <div>
            <label
              for="normativ"
              class="block text-sm font-medium text-gray-700"
              >Normativ</label
            >
            <div class="mt-1">
              <input
                v-model="pricing.norm"
                type="number"
                name="normativ"
                min="0"
                id="normativ"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :placeholder="[lastPricing ? lastPricing.norm : '0.00']" />
            </div>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-5">
        <button
          type="button"
          class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="$emit('close')">
          Cancel
        </button>
        <button
          type="button"
          class="relative inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="savePrices">
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
  data: () => ({
    formatter: {
      date: 'YYYY-MM-DD',
      month: 'MMM'
    },
    pricing: {
      date: dayjs().format('YYYY-MM-DD')
    }
  }),
  components: {
    Modal
  },
  computed: {
    lastPricing() {
      if (this.item.pricing[0]) return this.item.pricing[0]
      return { retail_price: this.item.price, purchase_price: this.item.price, norm: 1 }
    }
  },
  mounted() {
    this.pricing.inventory_id = this.item.id
  },
  methods: {
    savePrices() {
      if (!this.pricing.retail_price) this.pricing.retail_price = this.lastPricing.retail_price
      if (!this.pricing.purchase_price) this.pricing.purchase_price = this.lastPricing.purchase_price
      if (!this.pricing.norm) this.pricing.norm = this.lastPricing.norm
      this.pricing.date = dayjs(this.pricing.date + ' ' + dayjs().format('HH:mm:ss')).format('YYYY-MM-DD HH:mm:ss')
      this.$emit('save', this.pricing)
    }
  }
}
</script>
