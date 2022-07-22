import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import Portal from 'vue3-portal'
import App from './App.vue'
import router from './router'
import store from './store'

const app = createApp(App)
  .use(router)
  .use(store)
  .use(Portal)

app.config.globalProperties.$filters = {
  formatPrice(value) {
    return parseInt(value).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  }
}

app.mount('#app')
