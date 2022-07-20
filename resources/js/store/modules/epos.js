const general = {
  state: () => ({
    activeParentCategoryId: 0,
    activeCategory: null,
    parentCategories: [
      { id: 0, name: 'Šank' },
      { id: 1, name: 'Kuhinja' },
    ],
    categories: [],
    inventory: [],
  }),

  actions: {
    loadEPOS( { dispatch }) {
      dispatch('getCategories')
      dispatch('getInventory')
    },
    getCategories( { commit, state }) {
      axios.get('/api/categories/')
        .then((res) => {
          commit('setCategories', res.data)
        })
    },
    getInventory( { commit, state }) {
      axios.get('/api/inventory/')
        .then((res) => {
          commit('setInventory', res.data)
        })
    },
    storeActiveCategory( { commit, state }, category ){
        commit( 'setActiveCategory', category )
    },
    storeActiveParentCategoryId( { commit, state }, id ){
        commit( 'setActiveParentCategoryId', id )
    },
  },

  mutations: {
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
  },

  getters: {
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
  }
}

export default general
