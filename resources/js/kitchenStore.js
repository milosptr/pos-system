import { createStore } from 'vuex'

const kitchenStore = createStore({
    state() {
        return {
            activeOrders: [],
            readyOrders: [],
            activeTab: 'active',
            pendingToggles: 0,
        }
    },
    actions: {
        fetchOrders({ commit, state }) {
            if (state.pendingToggles > 0) return
            axios.get('/api/kitchen/orders').then(response => {
                if (state.pendingToggles > 0) return
                commit('setOrders', response.data)
            })
        },
        markReady({ dispatch }, id) {
            axios.post(`/api/kitchen/orders/${id}/ready`).then(() => {
                dispatch('fetchOrders')
            })
        },
        undoReady({ dispatch }, id) {
            axios.post(`/api/kitchen/orders/${id}/undo`).then(() => {
                dispatch('fetchOrders')
            })
        },
        async toggleItemDone({ state, commit, dispatch }, { orderId, itemId }) {
            const order = state.activeOrders.find(o => o.id === orderId)
                || state.readyOrders.find(o => o.id === orderId)
            if (order) {
                const item = order.items.find(i => i.id === itemId)
                if (item) item.is_done = !item.is_done
            }
            commit('startToggle')
            try {
                await axios.post(`/api/kitchen/items/${itemId}/toggle-done`)
            } finally {
                commit('endToggle')
                if (state.pendingToggles === 0) {
                    dispatch('fetchOrders')
                }
            }
        },
    },
    mutations: {
        setOrders(state, { active, ready }) {
            state.activeOrders = active
            state.readyOrders = ready
        },
        setActiveTab(state, tab) {
            state.activeTab = tab
        },
        startToggle(state) {
            state.pendingToggles++
        },
        endToggle(state) {
            state.pendingToggles--
        },
    },
    getters: {
        activeOrders: state => state.activeOrders,
        readyOrders: state => state.readyOrders,
        activeTab: state => state.activeTab,
    },
})

export default kitchenStore
