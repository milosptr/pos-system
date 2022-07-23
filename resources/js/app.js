import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import Portal from 'vue3-portal'
import vClickOutside from "click-outside-vue3"
import App from './App.vue'
import router from './router'
import store from './store'

const app = createApp(App)
  .use(router)
  .use(store)
  .use(Portal)
  .use(vClickOutside)


const backoffice = createApp(App)
  .use(Portal)

app.config.globalProperties.$filters = {
  formatPrice(value) {
    if(!value)
      return 0
    return parseInt(value).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  }
}

app.mount('#app')
backoffice.mount('#backoffice')
