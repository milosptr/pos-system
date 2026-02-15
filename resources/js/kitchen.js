import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import kitchenStore from './kitchenStore'
import KitchenDisplay from './views/KitchenDisplay.vue'

const kitchen = createApp(KitchenDisplay)
  .use(kitchenStore)

kitchen.config.globalProperties.$filters = {
  formatPrice(value) {
    if (!value) return 0
    return parseInt(value)
      .toFixed(0)
      .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  },
}

kitchen.mount('#kitchen')
