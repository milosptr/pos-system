const general = {
  state: () => ({
    activeArea: null,
    tables: [],
    areas: [
      {id: 0, name: 'Sala', pattern: '/images/sala.jpg'},
      {id: 1, name: 'Basta', pattern: '/images/basta.jpg'},
    ],
    tabs: [
      {id: 0, name: 'Promet', url: '/transactions', icon: '/images/coins.svg'},
      {id: 1, name: 'Racuni', url: '/invoices', icon: '/images/receipt.svg'},
    ],
  }),

  actions: {
    getTables( { commit, state }) {
      axios.get('/api/tables/' + state.activeArea.id)
        .then((res) => {
          commit('setTables', res.data.data)
        })
    },
    setDefaultActiveArea( { dispatch, commit, state } ){
        commit( 'setActiveArea', state.areas[0] )
        dispatch('getTables')
    },
    storeActiveArea( { dispatch, commit, state }, area ){
        commit( 'setActiveArea', area )
        dispatch('getTables')
    },
  },

  mutations: {
      setActiveArea( state , area ){
          state.activeArea = area
      },
      setTables( state, tables ) {
        state.tables = tables
      },
  },

  getters: {
      getActiveArea( state ){
        return state.activeArea;
      },
      getAreas( state ) {
        return state.areas
      },
      getTabs( state ) {
        return state.tabs
      },
      getTables( state ) {
        return state.tables
      },
  }
}

export default general
