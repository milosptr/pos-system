<template>
<div>
    <div
        v-for="(user, idx) in $store.getters.users"
        :key="idx"
    >
    {{ user }}
    </div>
</div>
</template>
<script>
export default {
    name: 'BackofficeUsers',
    mounted() {
        this.$store.dispatch('getUsers')
    }
}
</script>
<style scoped>

</style>
