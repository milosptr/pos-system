import { createStore } from 'vuex'

const kitchenStore = createStore({
    state() {
        return {
            activeOrders: [],
            readyOrders: [],
            activeTab: 'active',
        }
    },
    actions: {
        fetchOrders({ commit }) {
            axios.get('/api/kitchen/orders').then(response => {
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
        toggleItemDone({ state }, { orderId, itemId }) {
            const order = state.activeOrders.find(o => o.id === orderId)
                || state.readyOrders.find(o => o.id === orderId)
            if (order) {
                const item = order.items.find(i => i.id === itemId)
                if (item) item.is_done = !item.is_done
            }
            axios.post(`/api/kitchen/items/${itemId}/toggle-done`)
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
    },
    getters: {
        activeOrders: state => state.activeOrders,
        readyOrders: state => state.readyOrders,
        activeTab: state => state.activeTab,
    },
})

export default kitchenStore
