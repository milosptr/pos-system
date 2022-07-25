<template>
<div>
    <div
        v-for="(table, idx) in $store.getters.tables"
        :key="idx"
    >
    {{ table }}
    </div>
</div>
</template>
<script>
export default {
    name: "BackofficeTables",
    mounted(){
        this.$store.dispatch('getTables')
    }
}
</script>
<style scoped>

</style>
