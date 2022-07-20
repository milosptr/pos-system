<template>
  <div>
    Single Table
  </div>
</template>

<script>
  export default {
    data: () => ({
      order: []
    }),
    mounted() {
      axios.get('/api/orders/table/' + this.$route.params.id)
        .then((res) => {
          if(res.data && res.data.order)
            this.order = res.data.order
        })
    }
  }
</script>
