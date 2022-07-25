import axios from "axios"

const backoffice = {
    state: () => ({
        revenue: [],
        stats: null,
        activeTableOrders: [],
        categories: [],
        inventory: [],
        users: [],
        tables: [],
        orders: [],
        invoices: [],
        ordersFilters: {
            date: null
        }
    }),

    actions: {
        getRevenue({ commit }) {
            axios.get('/api/revenue')
                .then( (res) => {
                    commit('setRevenue', res.data)
                })
        },
        getStats({ commit }) {
            axios.get('/api/stats')
                .then( (res) => {
                    commit('setStats', res.data)
                })
        },
        getActiveTableOrders({ commit }) {
            axios.get('/api/active-orders')
                .then( (res) => {
                    commit('setActiveTableOrders', res.data.data)
                })
        },
        getCategories({ commit }) {
            axios.get('/api/categories')
                .then( (res) => {
                    commit('setCategories', res.data.data)
                })
        },
        getInventory({ commit }) {
            axios.get('/api/inventory/sorted')
                .then( (res) => {
                    commit('setInventory', res.data.data)
                })
        },
        getUsers({ commit }) {
            axios.get('/api/users')
                .then( (res) => {
                    commit('setUsers', res.data.data)
                })
        },
        getTables({ commit }) {
            axios.get('/api/tables')
                .then( (res) => {
                    commit('setTables', res.data.data)
                })
        },
        getOrders({ commit }) {
            axios.get('/api/orders')
                .then( (res) => {
                    commit('setOrders', res.data.data)
                })
        },
        getInvoices({ commit }) {
            axios.get('/api/invoices')
                .then( (res) => {
                    commit('setInvoices', res.data.data)
                })
        },
    },

    mutations: {
        setRevenue(state, revenue) {
            state.revenue = revenue
        },
        setStats(state, stats) {
            state.stats = stats
        },
        setActiveTableOrders(state, tableOrders) {
            state.activeTableOrders = tableOrders
        },
        setCategories(state, categories) {
            state.categories = categories
        },
        setInventory(state, inventory) {
            state.inventory = inventory
        },
        setUsers(state, users) {
            state.users = users
        },
        setTables(state, tables) {
            state.tables = tables
        },
        setOrders(state, orders) {
            state.orders = orders
        },
        setInvoices(state, invoices) {
            state.invoices = invoices
        },
    },

    getters: {
        activeTableOrders: (state) => state.activeTableOrders,
        revenue: (state) => state.revenue,
        categories: (state) => state.categories,
        inventory: (state) => state.inventory,
        users: (state) => state.users,
        tables: (state) => state.tables,
        orders: (state) => state.orders,
        invoices: (state) => state.invoices,
        stats: (state) => state.stats,
    }
}

export default backoffice
