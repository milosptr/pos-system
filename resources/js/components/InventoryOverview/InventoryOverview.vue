<template>
  <div class="p-4">
    <div class="flex items-center gap-2 text-center">
      <div
        v-for="parent in parentCategories"
        :key="parent.id"
        class="w-48 px-6 py-3 bg-primary rounded-sm text-white text-lg uppercase font-medium"
        :class="[parent.id === activeParentCategoryId ? 'opacity-100' : 'opacity-70']"
        @click="setParentCategory(parent.id)"
      >
        {{ parent.name }}
      </div>
      <div class="flex ml-auto gap-2">
        <div
          v-if="canDivideInHalf"
          class="bg-gray-500 flex items-center justify-center text-lg uppercase font-medium OneHalf ml-auto"
          @click="halfPortion"
        >
          <img :src="$filters.imgUrl('onehalf.svg')" alt="half" width="35"/>
        </div>
        <router-link to="/"
          class="w-32 px-6 py-3 bg-red-600 text-center text-white text-lg uppercase font-medium"
        >
          Nazad
        </router-link>
      </div>
    </div>
    <div v-if="showCategories" class="grid grid-cols-4 gap-2 mt-4">
      <div
        v-for="category in categories"
        :key="category.id"
        @click="setActiveCategory(category.id)"
        class="InventoryBox flex items-center justify-center rounded-sm"
        >
        <div class="text-center font-bold uppercase">
          {{ category.name }}
        </div>
      </div>
    </div>
    <div v-if="!showCategories" class="grid grid-cols-5 gap-2 mt-4">
      <div
        v-for="item in inventory"
        :key="item.id"
        class="InventoryBox InventoryItem flex items-center justify-center rounded-sm"
        @click="addToOrder(item)"
        >
        <div class="text-center font-bold">
          {{ item.name }}
        </div>
      </div>
    </div>
    <QuantityModal v-if="showQuantityModal" @close="showQuantityModal = false" />
  </div>
</template>

<script>
  import QuantityModal from '../Modals/QuantityModal.vue'

  export default {
  components: { QuantityModal },
    data: () => ({
      showCategories: true,
      showQuantityModal: false,
      customQty: null,
    }),
    computed: {
      activeParentCategoryId() {
        return this.$store.getters.activeParentCategoryId
      },
      parentCategories() {
        return this.$store.getters.parentCategories
      },
      categories() {
        return this.$store.getters.filteredCategories
      },
      inventory() {
        return this.$store.getters.inventoryForCategory
      },
      canDivideInHalf() {
        return this.$store.getters.lastItemInOrder && this.$store.getters.lastItemInOrder.sold_by === 1
      },
      canSelectGrams() {
        return this.$store.getters.lastItemInOrder && this.$store.getters.lastItemInOrder.sold_by === 2
      },
    },
    mounted() {

    },
    methods: {
      setParentCategory(id) {
        this.$store.commit('setActiveParentCategoryId', id)
        this.showCategories = true
      },
      setActiveCategory(id) {
        this.$store.commit('setActiveCategory', id)
        this.showCategories = false
      },
      halfPortion() {
        this.$store.commit('lastOrderHalfPortion')
      },
      addToOrder(item) {
        this.$store.commit('setOrder', {...item, table_id: this.$route.params.id})
        if(item.sold_by === 2)
          this.showQuantityModal = true
      }
    }
  }
</script>

<style scoped>
  .InventoryBox {
    padding: 0 8px;
    aspect-ratio: 5/3;
    background: #d5d5d5;
    font-size: 23px;
    font-weight: 500;
    line-height: 1.2em;
  }

  .InventoryBox:active {
    background: #777;
  }

  .InventoryBox.InventoryItem {
    font-size: 20px;
  }

  .OneHalf {
    height: 52px;
    width: 72px;
  }
</style>
