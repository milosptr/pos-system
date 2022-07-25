import { createStore } from 'vuex'
import backoffice from './store/modules/backoffice'

const backofficeStore = createStore({
    modules: {
        backoffice
    }
})

export default backofficeStore
