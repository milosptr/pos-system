<template>
    <!-- <BackofficeNavigation /> -->
    <div class="flex justify-center">
        <router-view />
    </div>
</template>

<script>
import BackofficeNavigation from './components/Backoffice/BackofficeNavigation.vue'
export default {
    name: "Backoffice",
    components: {
        BackofficeNavigation
    }
}
</script>

<style scoped>

</style>
