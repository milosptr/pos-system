import { createStore } from 'vuex'
import general from './store/modules/general'
import epos from './store/modules/epos'
import printing from './store/modules/printing'

const store = createStore({
	modules: {
    general,
    epos,
    printing,
	}
});

export default store
