<template>
  <div class="p-4">
    <div class="grid grid-cols-4 gap-2 text-center">
      <div
        v-for="parent in parentCategories"
        :key="parent.id"
        class="px-6 py-3 bg-primary rounded-sm text-white text-lg uppercase font-medium"
        :class="[parent.id === activeParentCategoryId ? 'opacity-100' : 'opacity-70']"
        @click="setParentCategory(parent.id)"
      >
        {{ parent.name }}
      </div>
    </div>
    <div v-if="showCategories" class="grid grid-cols-4 gap-2 mt-4">
      <div
        v-for="category in categories"
        :key="category.id"
        @click="setActiveCategory(category.id)"
        class="InventoryBox flex items-center justify-center rounded-sm"
        >
        <div class="text-center font-bold">
          {{ category.name }}
        </div>
      </div>
    </div>
    <div v-if="!showCategories" class="grid grid-cols-5 gap-2 mt-4">
      <div
        v-for="item in inventory"
        :key="item.id"
        class="InventoryBox flex items-center justify-center rounded-sm"
        >
        <div class="text-center font-bold">
          {{ item.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      showCategories: true,
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
    },
    mounted() {

    },
    methods: {
      setParentCategory(id) {
        this.$store.dispatch('storeActiveParentCategoryId', id)
        this.showCategories = true
      },
      setActiveCategory(id) {
        this.$store.dispatch('storeActiveCategory', id)
        this.showCategories = false
      }
    }
  }
</script>

<style scoped>
  .InventoryBox {
    padding: 0 8px;
    aspect-ratio: 5/3;
    background: #eee;
    font-size: 18px;
    font-weight: 500;
  }

  .InventoryBox:active {
    background: #777;
  }
</style>
