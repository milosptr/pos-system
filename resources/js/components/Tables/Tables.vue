<template>
  <div class="relative w-full TablesWrapper">
    <router-link
      v-for="table in tables"
      :key="table.id"
      :to="'/table/' + table.id"
      :style="'left: ' + table.position_x * 2 + '%; top: ' + table.position_y * 4 + '%;'"
      class="SingleTable"
    >
      <div class="relative flex items-center justify-center h-full text-3xl font-bold">
        {{ table.table_number}}
        <div class="absolute bottom-0 left-0 w-full text-center text-xl font-semibold">
          4.080,00
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
    methods: {}
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
    width: 10%;
    border: 8px solid #56b44f;
    background: #fff;
    margin: 1%;
    position: absolute;
  }

  @media(max-width: 1400px) {
    .SingleTable {
      width: 17%;
    }
  }
  @media(max-width: 1024px) {
    .SingleTable {
      width: 18%;
    }
  }
</style>
