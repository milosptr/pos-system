const general = {
  state: () => ({
    invoices: [],
    tasks: [],
    activeInvoice: null,
    openedDeleteButton: null
  }),

  actions: {
    getInvoices({ commit }) {
      axios.get('/api/invoices/today')
        .then((res) => {
          commit('setInvoices', res.data.data)
        })
    },
    refundInvoice( {commit, state }, data) {
      axios.post(`/api/invoices/${state.activeInvoice.id}/refund`, data)
        .then((res) => {
          commit('setInvoices', res.data.data)
          commit('setActiveInvoice', state.activeInvoice.id)
        })
    },
    getTasks({ commit }) {
      axios.get('/api/tasks/today')
        .then( (res) => {
            commit('setTasks', res.data.data)
        })
    },
  },

  mutations: {
    setInvoices( state, invoices ) {
      state.invoices = invoices
    },
    setActiveInvoice( state, id ) {
      state.activeInvoice = state.invoices.find((i) => i.id === id)
    },
    setOpenedDeleteButton( state, btn ) {
      state.openedDeleteButton = btn
    },
    setTasks(state, tasks) {
      state.tasks = tasks
    },
  },

  getters: {
    invoices(state) {
      return state.invoices
    },
    activeInvoice(state) {
      return state.activeInvoice
    },
    openedDeleteButton(state) {
      return state.openedDeleteButton
    },
    tasks: (state) => state.tasks,
  }
}

export default general
