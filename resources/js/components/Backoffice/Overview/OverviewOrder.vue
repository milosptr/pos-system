<template>
    <div class="flex flex-col ml-4 w-full">
        <div
            v-for="(orderItem, idx) in order.order"
            :key="idx"
            class="flex flex-col border rounded px-8 py-2 mb-2"
        >
            <div class="mr-4"> {{ orderItem.name }} x {{ orderItem.qty }} </div>
            <div class="mr-4"> {{ orderItem.price }} </div>
        </div>
        <div> Total: {{ order.total }} </div>
    </div>
</template>
<script>
export default {
    name: "OverviewOrder",
    props: {
        order: {
            type: Object,
            default: null
        }
    }
}
</script>
<style scoped>

</style>
