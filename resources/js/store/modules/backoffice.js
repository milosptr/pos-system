import axios from "axios"

const backoffice = {
    state: () => ({
        reports: null,
        reportFilters: {
          date: '',
          sort: '',
        },
        stats: null,
        activeTableOrders: [],
        activeOrder: null,
        activeInventoryPricing: null,
        categories: [],
        clients: [],
        inventory: [],
        users: [],
        tables: [],
        orders: [],
        invoices: [],
        thirdPartyInvoices: [],
        thirdPartyOrders: [],
        tasks: [],
        ordersFilters: {
            date: null
        },
        reportsActiveTab: 0,
        pagination: {},
        thirdPartyPagination: {},
    }),

    actions: {
        getReports({ commit, state }, type) {
          const params = new URLSearchParams(state.reportFilters);
          axios.get('/api/backoffice/reports/'+ state.reportsActiveTab + '/?' + params.toString())
          .then((res) => {
            commit('setReports', res.data)
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
        getClients({ commit }) {
          axios.get('/api/backoffice/clients')
            .then((res) => {
              commit('setClients', res.data)
            })
        },
        getInventory({ commit }, filters) {
          const params = new URLSearchParams(filters);
          return new Promise((resolve, reject) => {
            axios.get('/api/backoffice/inventory/all?' + params.toString())
              .then( (res) => {
                commit('setInventory', res.data.data)
                resolve(res)
              })
              .catch((error) => {
                reject(error)
              })
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
        getInvoices({ commit, state }) {
          const params = new URLSearchParams(state.reportFilters);
          axios.get('/api/invoices?' + params.toString())
              .then( (res) => {
                  commit('setInvoices', res.data.data)
                  if(res.data.links)
                    commit('setPagination', { links: res.data.links, meta: res.data.meta })
              })
        },
        getThirdPartyInvoices({ commit, state }) {
          const params = new URLSearchParams(state.reportFilters);
          axios.get('/api/backoffice/third-party-invoices?' + params.toString())
              .then( (res) => {
                  commit('setThirdPartyInvoices', res.data.data)
                  if(res.data.links)
                    commit('setThirdPartyPagination', { links: res.data.links, meta: res.data.meta })
              })
        },
        getThirdPartyOrders({ commit }) {
            axios.get('/api/backoffice/third-party-orders')
                .then( (res) => {
                    commit('setThirdPartyOrders', res.data.data)
                })
        },
        deleteThirdPartyInvoice({ dispatch }, invoiceId) {
          return axios.delete('/api/backoffice/third-party-invoices/' + invoiceId)
            .then(() => {
              dispatch('getThirdPartyInvoices')
            })
        },
        getTasks({ commit }) {
            axios.get('/api/tasks')
                .then( (res) => {
                    commit('setTasks', res.data.data)
                })
        },
    },

    mutations: {
        setReports(state, reports) {
            state.reports = reports
        },
        setReportsSortBy(state, column) {
          const direction = state.reportFilters.sort?.includes('asc') ? 'desc' : 'asc'
          state.reportFilters.sort = column + '.' + direction
        },
        setReportFilters(state, filter) {
          state.reportFilters[filter.key] = filter.value
          if(!filter.value) delete state.reportFilters[filter.key]
        },
        resetReportFilters(state) {
          state.reportFilters = {}
        },
        setStats(state, stats) {
            state.stats = stats
        },
        setActiveTableOrders(state, tableOrders) {
            state.activeTableOrders = tableOrders
            if(state.activeOrder && state.activeOrder.id)
              state.activeOrder = state.activeTableOrders.find((o) => o.id === state.activeOrder.id)
        },
        setActiveOrder(state, table) {
          state.activeOrder = table
        },
        setCategories(state, categories) {
            state.categories = categories
        },
        setClients(state, clients) {
            state.clients = clients
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
        setThirdPartyInvoices(state, invoices) {
            state.thirdPartyInvoices = invoices
        },
        setThirdPartyOrders(state, orders) {
            state.thirdPartyOrders = orders
        },
        setThirdPartyPagination(state, pagination) {
            state.thirdPartyPagination = pagination
        },
        setTasks(state, tasks) {
            state.tasks = tasks
        },
        setReportsActiveTab(state, tab) {
            state.reportsActiveTab = tab
        },
        setActiveInventoryPricing(state, inventoryPricing) {
          state.activeInventoryPricing = inventoryPricing
        },
        setPagination(state, pagination) {
          state.pagination = pagination
        },
    },

    getters: {
        activeTableOrders: (state) => state.activeTableOrders,
        activeOrder: (state) => state.activeOrder,
        activeInventoryPricing: (state) => state.activeInventoryPricing,
        reports: (state) => state.reports,
        categories: (state) => state.categories,
        inventory: (state) => state.inventory,
        users: (state) => state.users,
        tables: (state) => state.tables,
        orders: (state) => state.orders,
        invoices: (state) => state.invoices,
        thirdPartyInvoices: (state) => state.thirdPartyInvoices,
        thirdPartyOrders: (state) => state.thirdPartyOrders,
        thirdPartyOrdersGroupedByTable: (state) => {
            const grouped = {}

            state.thirdPartyOrders.forEach(order => {
                const key = order.table_id ?? 'ungrouped'

                if (!grouped[key]) {
                    grouped[key] = {
                        table_id: order.table_id,
                        table_name: order.table_name,
                        orders: [],
                        total: 0,
                        active_total: 0,
                    }
                }

                grouped[key].orders.push(order)
                grouped[key].total += order.total
                grouped[key].active_total += order.active_total ?? order.total
            })

            return Object.values(grouped)
        },
        thirdPartyPagination: (state) => state.thirdPartyPagination,
        tasks: (state) => state.tasks,
        stats: (state) => state.stats,
        clients: (state) => state.clients,
        reportsActiveTab: (state) => state.reportsActiveTab,
        reportFilters: (state) => state.reportFilters,
        pagination: (state) => state.pagination,
        totalActiveTableOrders: (state) => state.activeTableOrders.reduce((a, v) => a + v.total, 0),
        totalRevenue: (state) => {
          const total = state.stats && state.stats[0].stat ? state.stats[0].stat : 0
          const totalActiveTableOrders = state.activeTableOrders.reduce((a, v) => a + v.total, 0)
          return parseInt(totalActiveTableOrders) + parseInt(total)
        },
    }
}

export default backoffice
