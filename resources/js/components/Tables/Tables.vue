<template>
  <div class="relative w-full TablesWrapper grid grid-cols-6 grid-rows-6 pt-1 pb-3">
    <router-link
      v-for="table in tables"
      :key="table.id"
      :to="'/table/' + table.id"
      :style="tablePosition(table)"
      class="SingleTable"
      :class="tableClasses(table)"
      @click="setActiveTable(table)"
    >
      <div class="relative flex items-center justify-center h-full text-3xl font-bold">
        {{ tableNumber(table.table_number) }}
        <div v-if="table.total" class="absolute bottom-0 left-0 w-full text-center text-xl font-semibold mb-1" style="line-height: 1">
          {{ $filters.formatPrice(table.total) }},00
        </div>
      </div>
    </router-link>
  </div>
</template>

<script>
  export default {
    data: () => ({
    }),
    computed: {
      tables() {
        return this.$store.getters.getTables
      }
    },
    mounted() {
      this.$store.commit('clearOrder')
    },
    methods: {
      setActiveTable(table) {
        this.$store.commit('setActiveTable', table)
      },
      tablePosition(table) {
        let innerWidth = window.innerWidth
        let innerHeight = window.innerHeight - 108
        let colWidth = innerWidth / 6
        let colHeight = innerHeight / 6
        let boxWidth = table.size ? 205 : 135
        let boxHeight = table.size ? 115 : 135
        if(table.rotate) {
          boxWidth = 135
          boxHeight = 205
        }
        let marginTop = 0
        let marginLeft = (colWidth - boxWidth) / 2
        const gridArea = `grid-area: ${table.position_y}/${table.position_x};`

        if(table.position_x_middle) {
          marginLeft = colWidth - boxWidth / 2
        }

        if(table.position_y_middle) {
          marginTop = colHeight - boxHeight / 2
        }

        const margin = `margin: ${marginTop}px 0 0 ${marginLeft}px;`
        return [gridArea, margin];
      },
      tableClasses(table) {
        let classes = []
        if(table.total) classes.push('HasOrders')
        if(table.rotate) classes.push('Rotate')
        classes.push(table.size ? 'Big' : 'Small')
        return classes
      },
      tableNumber(n) {
        if(n === 28)
          return 'N'
        if(n === 30)
          return 'Banket'
        return n
      },
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
    transition: all .3s ease-in-out;
    animation: scaleTable .3s ease-in-out forwards;
  }

  .SingleTable {
    width: 205px;
    height: 115px;
  }
  .SingleTable.Small {
    width: 135px;
    height: 135px;
  }
  .SingleTable.Rotate {
    height: 205px;
    width: 135px!important;
  }

  .SingleTable.HasOrders {
    border: 8px solid rgb(220, 38, 38);
  }

  @media(max-width: 1400px) {
    .SingleTable {
      width: 205px;
    }
    .SingleTable.Small {
      width: 130px;
    }
    .SingleTable.Rotate {
      width: 205px;
    }
  }

  @media(max-width: 1024px) {
    .SingleTable {
      transform: scale(0.8)!important;
    }
  }

  @keyframes scaleTable {
    0% {
      transform: scale(0);
    }
    100% {
      transform: scale(1);
    }
  }

</style>
