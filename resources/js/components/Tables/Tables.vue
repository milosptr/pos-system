<template>
  <div class="relative w-full TablesWrapper grid grid-cols-6 grid-rows-6 pt-1 sm:py-5">
    <router-link
      v-for="table in tables"
      :key="table.id"
      :to="'/table/' + table.id"
      :style="tablePosition(table)"
      class="SingleTable"
      :class="tableClasses(table)"
      @click="setActiveTable(table)">
      <div class="relative flex items-center justify-center h-full font-bold text-3xl">
        {{ tableNumber(table.table_number) }}
        <div
          v-if="table.total"
          class="absolute bottom-0 left-0 w-full text-center text-xl font-semibold mb-1"
          style="line-height: 1">
          {{ $filters.formatPrice(table.total) }},00
        </div>
      </div>
    </router-link>
  </div>
</template>

<script>
export default {
  data: () => ({}),
  computed: {
    tables() {
      return this.$store.getters.getTables.filter((t) => t.area === this.$store.getters.getActiveArea.id)
    }
  },
  mounted() {
    this.$store.commit('clearOrder')
    this.$store.dispatch('getTasks')
  },
  methods: {
    setActiveTable(table) {
      this.$store.commit('setActiveTable', table)
      this.$store.commit('setTable', table)
      this.$store.commit('resetPrinting')
    },
    tablePosition(table) {
      let innerWidth = window.innerWidth
      let innerHeight = window.innerHeight - 108
      let colWidth = innerWidth / 6
      let colHeight = innerHeight / 6
      let boxWidth = table.size ? 180 : 120
      let boxHeight = table.size ? 100 : 120
      if (table.rotate) {
        boxWidth = 120
        boxHeight = 180
      }
      let marginTop = table.size ? 0 : (colHeight - boxHeight) / 2
      marginTop = table.rotate ? marginTop - colHeight / 2 : marginTop
      let marginLeft = (colWidth - boxWidth) / 2
      const gridArea = `grid-area: ${table.position_y}/${table.position_x};`

      if (table.position_x_middle) {
        marginLeft = colWidth - boxWidth / 2
      }

      if (table.position_y_middle) {
        marginTop = colHeight - boxHeight / 2
      }

      const margin = `margin: ${marginTop}px 0 0 ${marginLeft}px;`
      return [gridArea, margin]
    },
    tableClasses(table) {
      let classes = []
      if (table.orders.length) classes.push('HasOrders')
      if (table.rotate) classes.push('Rotate')
      classes.push(table.size ? 'Big' : 'Small')
      return classes
    },
    tableNumber(n) {
      if (n === 28) return 'N'
      if (n === 30) return 'B1'
      if (n === 31) return 'B2'
      return n
    }
  }
}
</script>

<style scoped>
.TablesWrapper {
  height: calc(100vh - 108px);
}
.SingleTable {
  box-sizing: border-box;
  /* aspect-ratio: 16/9; */
  border: 8px solid #56b44f;
  background: #fff;
  margin: 1%;
  position: absolute;
  transition: all 0.3s ease-in-out;
}

.SingleTable {
  width: 180px;
  height: 100px;
}
.SingleTable.Small {
  width: 120px;
  height: 120px;
}
.SingleTable.Rotate {
  height: 180px;
  width: 120px !important;
}

.SingleTable.HasOrders {
  border: 8px solid rgb(220, 38, 38);
}

@media (max-width: 1400px) {
  .SingleTable {
    width: 180px;
  }
  .SingleTable.Small {
    width: 130px;
  }
  .SingleTable.Rotate {
    width: 180px;
  }
}

@media (max-width: 1024px) {
  .SingleTable {
    transform: scale(0.8) !important;
  }
}

@media (min-width: 1600px) {
  .SingleTable {
    transform: scale(1.1) !important;
  }
}

@keyframes scaleTable {
  0% {
    transform: scale(0);
  }
  100% {
    transform: scale(0.8);
  }
}
</style>
