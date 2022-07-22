const general = {
  state: () => ({
    invoices: [],
    activeInvoice: null,
  }),

  actions: {
    getInvoices({ commit }) {
      axios.get('/api/invoices')
        .then((res) => {
          commit('setInvoices', res.data.data)
        })
    },
    refundInvoice( {commit, state }) {
      axios.post(`/api/invoices/${state.activeInvoice.id}/refund`)
        .then((res) => {
          commit('setInvoices', res.data.data)
          commit('setActiveInvoice', state.activeInvoice.id)
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
  },

  getters: {
    invoices(state) {
      return state.invoices
    },
    activeInvoice(state) {
      return state.activeInvoice
    },
  }
}

export default general
