const general = {
  state: () => ({
    areas: [
      {id: 0, name: 'Sala', pattern: '/images/sala.jpg'},
      {id: 1, name: 'Bašta', pattern: '/images/basta.jpg'},
    ],
    parentCategories: [
      { id: 0, name: 'Šank' },
      { id: 1, name: 'Kuhinja' },
    ],
    activeArea: null,
    activeTable: null,
    activeCategory: null,
    activeParentCategoryId: 0,
    waiters: [],
    tables: [],
    categories: [],
    inventory: [],
    orders: [],
    order: [],
    selectedWaiterId: null,
    totalForTable: 0,
  }),

  actions: {
    loadEPOS( { dispatch }) {
      dispatch('getCategories')
      dispatch('getInventory')
      dispatch('getWaiters')
    },
    moveOrdersToTable( {dispatch}, data) {
      axios.post(`/api/orders/move/${data.from}/${data.to}`)
        .then(() => {
          dispatch('getTables')
        })
    },
    getWaiters( { commit }) {
      axios.get('/api/waiters')
        .then((res) => {
          commit('setWaiters', res.data.data)
        })
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
      axios.get('/api/categories')
        .then((res) => {
          commit('setCategories', res.data.data)
        })
    },
    getInventory( { commit, state }) {
      axios.get('/api/inventory')
        .then((res) => {
          commit('setInventory', res.data.data)
        })
    },
    storeRefundItem( {}, order) {
      let total = order.order.reduce((a, val) => a + (val.refund ? 0 : (val.qty * val.price)), 0)
      console.log({...order, total});
      axios.post(`/api/orders/${order.id}/refund-item`, {...order, total})
    },
    storeOrder( {commit, dispatch, state }, table_id) {
      let total = state.order.reduce((a, b) => a + b.price * b.qty, 0)
      axios.post('/api/orders', { table_id, order: state.order, total})
      .then((res) => {
          if(res.data.data.order.some((i) => i.should_print))
            dispatch('setPrintingOrder', res.data.data, { root: true })
          dispatch('getTables')
          dispatch('getTableOrders')
        })
    },
    cashOut( {commit, dispatch, state }, data) {
      let allItems = []
      let orders = []
      let refunded = []
      state.orders.forEach((o) => { allItems = [...allItems, ...o.order]})
      allItems.forEach((i) => {
        if(!i.refund) {
          let qty = allItems.filter((it) => it.id === i.id && !it.refund).reduce((a, val) => a + val.qty, 0)
          if(!orders.some((o) => o.id === i.id))
            orders.push({...i, qty })
        }
        if(i.refund) refunded.push(i)
      })
      orders = [...orders, ...refunded]
      axios.post('/api/invoices', {
        user_id: state.selectedWaiterId,
        order: orders,
        total: orders.reduce((a, b) => a + (b.refund ? 0 : (b.price * b.qty)), 0),
        ...data
      })
        .then((res) => {
          if(res.data.data.status)
          dispatch('setPrintingInvoice', res.data.data, { root: true })
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
    setWaiters( state, waiters ) {
      state.waiters = waiters
    },
    setSelectedWaiterId( state, id ) {
      state.selectedWaiterId = id
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
    lastOrderHalfPortion( state ) {
      const lastItem = state.order.at(-1)
      lastItem.qty = lastItem.qty === 0.5 ? lastItem.qty : (lastItem.qty - 0.5)
    },
    setLastOrderCustomQty( state, qty ) {
      const lastItem = state.order.at(-1)
      lastItem.qty = qty > 0 ? qty : 1
    },
    refundItem( state, data) {
      const order = state.orders.find((o) => o.id === data.order.id)
      const item = order.order.find((i) => i.id === data.item.id)
      item.refund = !item.refund
    },
    removeItemFromOrder( state, id ) {
      state.order = state.order.filter((i) => i.id !== id)
    },
    setOrder( state, order ) {
      let hasOrder = state.order.find((o) => o.id === order.id)
      if(hasOrder) hasOrder.qty += order.qty
      if(!hasOrder) hasOrder = order
      state.order = state.order.filter((o) => o.id !== order.id)

      state.order = [ ...state.order, hasOrder]
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
    lastItemInOrder( state ) {
      return state.order.at(-1)
    },
    waiters( state ) {
      return state.waiters
    },
    selectedWaiterId( state ) {
      return state.selectedWaiterId
    },
  }
}

export default general
