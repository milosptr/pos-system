import axios from 'axios'

const backoffice = {
  state: () => ({
    reports: null,
    reportFilters: {
      date: '',
      sort: ''
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
    tasks: [],
    ordersFilters: {
      date: null
    },
    reportsActiveTab: 0,
    pagination: {}
  }),

  actions: {
    getReports({ commit, state }, type) {
      const params = new URLSearchParams(state.reportFilters)
      axios.get('/api/backoffice/reports/' + state.reportsActiveTab + '/?' + params.toString()).then((res) => {
        commit('setReports', res.data)
      })
    },
    getStats({ commit }) {
      axios.get('/api/stats').then((res) => {
        commit('setStats', res.data)
      })
    },
    getActiveTableOrders({ commit }) {
      axios.get('/api/active-orders').then((res) => {
        commit('setActiveTableOrders', res.data.data)
      })
    },
    getCategories({ commit }) {
      axios.get('/api/categories').then((res) => {
        commit('setCategories', res.data.data)
      })
    },
    getClients({ commit }) {
      axios.get('/api/backoffice/clients').then((res) => {
        commit('setClients', res.data)
      })
    },
    getInventory({ commit }, filters) {
      const params = new URLSearchParams(filters)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/backoffice/inventory/all?' + params.toString())
          .then((res) => {
            commit('setInventory', res.data.data)
            resolve(res)
          })
          .catch((error) => {
            reject(error)
          })
      })
    },
    getUsers({ commit }) {
      axios.get('/api/users').then((res) => {
        commit('setUsers', res.data.data)
      })
    },
    getTables({ commit }) {
      axios.get('/api/tables').then((res) => {
        commit('setTables', res.data.data)
      })
    },
    getOrders({ commit }) {
      axios.get('/api/orders').then((res) => {
        commit('setOrders', res.data.data)
      })
    },
    getInvoices({ commit, state }) {
      const params = new URLSearchParams(state.reportFilters)
      axios.get('/api/invoices?' + params.toString()).then((res) => {
        commit('setInvoices', res.data.data)
        if (res.data.links) commit('setPagination', { links: res.data.links, meta: res.data.meta })
      })
    },
    getTasks({ commit }) {
      axios.get('/api/tasks').then((res) => {
        commit('setTasks', res.data.data)
      })
    }
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
      if (!filter.value) delete state.reportFilters[filter.key]
    },
    resetReportFilters(state) {
      state.reportFilters = {}
    },
    setStats(state, stats) {
      state.stats = stats
    },
    setActiveTableOrders(state, tableOrders) {
      state.activeTableOrders = tableOrders
      if (state.activeOrder && state.activeOrder.id)
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
    }
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
    }
  }
}

export default backoffice
