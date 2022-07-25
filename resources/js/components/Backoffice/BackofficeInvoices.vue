<template>
<div>
    <div
        v-for="(invoice, idx) in $store.getters.invoices"
        :key="idx"
    >
    {{ invoice }}
    </div>
</div>
</template>
<script>
export default {
    name: "BackofficeInvoices",
    mounted(){
        this.$store.dispatch('getInvoices')
    }
}
</script>
<style scoped>

</style>
