<template>
<div>
    <div
        v-for="(item, idx) in $store.getters.inventory"
        :key="idx"
    >
    {{ item }}
    </div>
</div>
</template>
<script>
export default {
    name: 'BackofficeInventory',
    mounted() {
        this.$store.dispatch('getInventory')
    }
}
</script>
<style scoped>

</style>
