import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import Portal from 'vue3-portal'
import vClickOutside from 'click-outside-vue3'
import VueClipboard from 'vue-clipboard2'
import LitepieDatepicker from 'litepie-datepicker'
import backofficeRouter from './backofficeRouter'
import backofficeStore from './backofficeStore'
import Backoffice from './Backoffice.vue'

const backoffice = createApp(Backoffice)
  .use(backofficeRouter)
  .use(backofficeStore)
  .use(Portal)
  .use(vClickOutside)
  .use(LitepieDatepicker)
  .use(VueClipboard)

backoffice.config.globalProperties.$filters = {
  formatPrice(value) {
    if (!value) return 0
    return parseInt(value)
      .toFixed(0)
      .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  },
  imgUrl(url) {
    return new URL('/images/' + url, import.meta.env.VITE_APP_URL).href
  }
}

backoffice.mount('#backoffice')
