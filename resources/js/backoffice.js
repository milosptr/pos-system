import './bootstrap'
import '../css/app.css'
import { createApp } from 'vue'
import backofficeRouter from './backofficeRouter'
import backofficeStore from './backofficeStore'
import Backoffice from './Backoffice.vue'

const backoffice = createApp(Backoffice)
backoffice.use(backofficeRouter)
    .use(backofficeStore)

backoffice.mount('#backoffice')
