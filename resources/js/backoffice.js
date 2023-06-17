import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import Portal from 'vue3-portal'
import vClickOutside from "click-outside-vue3"
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
  formatPrice(value, double = false) {
    if(!value)
      return 0
    if(double)
      return parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
    return parseInt(value).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  },
  formatDate(value, withTime = false) {
    if(!value)
      return ''
    const config = { year: 'numeric', month: '2-digit', day: '2-digit' }
    if(withTime) {
      config.hour = 'numeric'
      config.minute = 'numeric'
    }

    return new Date(value).toLocaleDateString('sr-RS', config)
  },
  imgUrl(url) {
    return new URL('/images/' + url, import.meta.env.VITE_APP_URL).href
  }
}

backoffice.mount('#backoffice')
