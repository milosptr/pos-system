import { createStore } from 'vuex'
import backoffice from './store/modules/backoffice'
import printing from './store/modules/printing'

const backofficeStore = createStore({
  modules: {
    backoffice,
    printing
  }
})

export default backofficeStore
