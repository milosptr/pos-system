const general = {
  state: () => ({
    areas: [
      {id: 0, name: 'Sala', pattern: '/images/sala.jpg'},
      {id: 1, name: 'Basta', pattern: '/images/basta.jpg'},
    ],
    parentCategories: [
      { id: 0, name: 'Šank' },
      { id: 1, name: 'Kuhinja' },
    ],
    activeArea: null,
    activeTable: null,
    activeCategory: null,
    activeParentCategoryId: 0,
    tables: [],
    categories: [],
    inventory: [],
    orders: [],
    order: [],
    selectedUserId: 1,
    totalForTable: 0,
  }),

  actions: {
    loadEPOS( { dispatch }) {
      dispatch('getCategories')
      dispatch('getInventory')
    },
    getTables( { commit, state }) {
      axios.get('/api/tables/' + state.activeArea.id)
        .then((res) => {
          commit('setTables', res.data.data)
        })
    },
    getTableOrders( {commit, state} ) {
      axios.get('/api/orders/table/' + state.activeTable.id)
        .then((res) => {
          commit('clearOrder')
          commit('setNewOrders', res.data.data)
        })
    },
    getCurrentTable( { commit, dispatch }, id) {
      axios.get('/api/table/' + id)
        .then((res) => {
          commit('setActiveTable', res.data.data)
          dispatch('getTableOrders')
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
    getCategories( { commit, state }) {
      axios.get('/api/categories/')
        .then((res) => {
          commit('setCategories', res.data.data)
        })
    },
    getInventory( { commit, state }) {
      axios.get('/api/inventory/')
        .then((res) => {
          commit('setInventory', res.data.data)
        })
    },
    storeOrder( {commit, dispatch, state }, table_id) {
      let total = state.order.reduce((a, b) => a + b.price * b.qty, 0)
      axios.post('/api/orders', { table_id, order: state.order, total})
        .then(() => {
          dispatch('getTables')
        })
    },
    cashOut( {commit, dispatch, state }, table_id) {
      let orders = []
      state.orders.forEach((o) => { orders = [...orders, ...o.order]})
      axios.post('/api/invoices', {
        user_id: state.selectedUserId,
        table_id,
        order: orders,
        total: orders.reduce((a, b) => a + b.price * b.qty, 0)
      })
        .then((res) => {
          dispatch('getTables')
        })
    },
  },

  mutations: {
    setActiveArea( state , area ){
      state.activeArea = area
    },
    setTables( state, tables ) {
      state.tables = tables
    },
    setActiveTable( state, table ) {
      state.activeTable = table
    },
    setActiveParentCategoryId( state , id ){
        state.activeParentCategoryId = id
    },
    setActiveCategory( state , category ){
        state.activeCategory = category
    },
    setCategories( state, categories ) {
      state.categories = categories
    },
    setInventory( state, inventory ) {
      state.inventory = inventory
    },
    setNewOrders( state, orders ) {
      state.orders = orders
    },
    setOrders( state, orders ) {
      state.orders = [ ...state.orders, ...orders]
    },
    setOrder( state, order ) {
      let shouldAdd = true
      state.order = state.order.map((o) => {
        if(o.id === order.id) {
          o.qty += 1
          shouldAdd = false
        }
        return o
      })

      if(shouldAdd) state.order = [ ...state.order, order]
    },
    clearOrder( state ) {
      state.order = []
      state.orders = []
    }
  },

  getters: {
    getActiveArea( state ){
      return state.activeArea;
    },
    getAreas( state ) {
      return state.areas
    },
    getTables( state ) {
      return state.tables
    },
    activeTable( state ) {
      return state.activeTable
    },
    activeCategory( state ){
      return state.activeCategory;
    },
    parentCategories( state ) {
      return state.parentCategories
    },
    categories( state ) {
      return state.categories
    },
    filteredCategories( state ) {
      return state.categories.filter((c) => c.parent_id === state.activeParentCategoryId)
    },
    inventory( state ) {
      return state.inventory
    },
    inventoryForCategory( state ) {
      return state.inventory.filter((i) => i.category_id === state.activeCategory)
    },
    activeParentCategoryId( state ) {
      return state.activeParentCategoryId
    },
    orders( state ) {
      return state.orders
    },
    order( state ) {
      return state.order
    },
  }
}

export default general
