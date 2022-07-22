<template>
  <div class="relative w-full TablesWrapper grid grid-cols-6 grid-rows-6">
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
        <div v-if="table.total" class="absolute bottom-0 left-0 w-full text-center text-xl font-semibold" style="line-height: 1">
          {{ $filters.formatPrice(table.total) }} RSD
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
        const gridArea = `grid-area: ${table.position_y}/${table.position_x};`
        const defaultMargin = table.size ? 'margin: 0 7px;' : 'margin: 6px 45px;'
        const middleYPosition = table.position_y_middle ? (table.size ? 'margin: 60px 0 0 8px;' : `margin: 50px 0 0 46px;`) : defaultMargin
        const middleXPosition = table.position_x_middle ? (table.size ? 'margin: 0px 0 0 120px;' : `margin: 6px 158px;`) : defaultMargin
        const middleXYPosition = table.position_x_middle && table.position_y_middle ? (table.size ? 'margin: 60px 0 0 120px;' : `margin: 50px 158px;`) : defaultMargin
        const middlePosition = table.position_x_middle && table.position_y_middle ? middleXYPosition : (table.position_x_middle ? middleXPosition : middleYPosition)
        return [gridArea, middlePosition];
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
    aspect-ratio: 16/9;
    /* height: 13%; */
    width: 9%;
    border: 8px solid #56b44f;
    background: #fff;
    margin: 1%;
    position: absolute;
  }

  .SingleTable.HasOrders {
    border: 8px solid rgb(220, 38, 38);
  }

  .SingleTable.Rotate {
    transform: rotate(90deg);
    aspect-ratio: 13/9;
  }

  .SingleTable.Rotate > div{
    transform: rotate(-90deg)
  }

  .SingleTable.Small {
    aspect-ratio: 1/1;
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
      width: 18%;
    }
  }
</style>
