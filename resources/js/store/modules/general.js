const general = {
  state: () => ({
    invoices: [],
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
    setOpenedDeleteButton( state, btn ) {
      state.openedDeleteButton = btn
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
    }
  }
}

export default general
