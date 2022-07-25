<template>
<div class="nav fixed">
    <div class="flex justify-items-center">
        <div>
            <router-link :to="{name: 'overview'}" ><ViewListIcon class="w-1/2"/></router-link>
        </div>
    </div>
</div>
</template>
<script>
import { ViewListIcon } from '@heroicons/vue/outline'

export default {
    name: 'DashboardNavigation',
    components: {
        ViewListIcon
    }
}
</script>
<style scoped>
.nav {
    height: 100%;
    width: 5%;
}
</style>
