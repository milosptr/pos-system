<template>
<div>
    <div
        v-for="(category, idx) in $store.getters.categories"
        :key="idx"
    >
    {{ category }}
    </div>
</div>
</template>
<script>
export default {
    name: "BackofficeCategories",
    mounted(){
        this.$store.dispatch('getCategories')
    }
}
</script>
<style scoped>

</style>
