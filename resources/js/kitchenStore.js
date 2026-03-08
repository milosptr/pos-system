import { createStore } from 'vuex'

const kitchenStore = createStore({
    state() {
        return {
            activeOrders: [],
            readyOrders: [],
            waiters: [],
            activeTab: 'active',
            pendingToggles: 0,
        }
    },
    actions: {
        fetchWaiters({ commit }) {
            fetch('http://192.168.200.30:81/public/employees')
                .then(response => response.json())
                .then(result => {
                    const waiters = (result.data || []).filter(
                        e => e.occupation === 0 && e.lastCheckin !== null
                    ).map(e => ({ id: e.id, name: e.name, color: e.color }))
                    commit('setWaiters', waiters)
                })
                .catch(error => console.log('fetchWaiters error', error))
        },
        assignWaiter({ state, commit }, { orderId, waiterName }) {
            const order = state.activeOrders.find(o => o.id === orderId)
            if (order) order.waiter_name = waiterName
            axios.post(`/api/kitchen/orders/${orderId}/assign-waiter`, { waiter_name: waiterName })
                .catch(error => console.log('assignWaiter error', error))
        },
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
        setWaiters(state, waiters) {
            state.waiters = waiters
        },
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
        waiters: state => state.waiters,
        activeOrders: state => state.activeOrders,
        readyOrders: state => state.readyOrders,
        activeTab: state => state.activeTab,
    },
})

export default kitchenStore
