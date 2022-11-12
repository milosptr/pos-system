import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import Portal from 'vue3-portal'
import vClickOutside from "click-outside-vue3"
import App from './App.vue'
import router from './router'
import store from './store'
import * as Sentry from "@sentry/vue"
import { BrowserTracing } from "@sentry/tracing"

const app = createApp(App)
  .use(router)
  .use(store)
  .use(Portal)
  .use(vClickOutside)

app.config.globalProperties.$filters = {
  formatPrice(value) {
    if(!value)
      return 0
    return parseInt(value).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  },
  imgUrl(url) {
    return new URL('/images/' + url, import.meta.env.VITE_APP_URL).href
  }
}

Sentry.init({
  app,
  dsn: "https://7ab1563e9f7e402fb49a68005d993c6d@o4504147121274880.ingest.sentry.io/4504147122978816",
  environment: 'localhost',
  integrations: [
    new BrowserTracing({
      routingInstrumentation: Sentry.vueRouterInstrumentation(router),
    }),
  ],
  tracesSampleRate: 1.0,
})

app.mount('#app')
