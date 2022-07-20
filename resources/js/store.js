import { createStore } from 'vuex'
import general from './store/modules/general'
import epos from './store/modules/epos'

const store = createStore({
	modules: {
    general,
    epos,
	}
});

export default store
