<template>
    <div class="flex mt-6 justify-center">
        <div class="flex justify-start">
            <div class="flex flex-col px-10 py-5 border border-solid rounded mr-4 drop-shadow-lg">
                <div class="font-bold">Ukupno</div>
                <div> {{ $store.getters.revenue.total }} RSD</div>
            </div>
            <div class="flex flex-col px-10 py-5 border border-solid rounded mr-4 drop-shadow-lg">
                <div class="font-bold">Storno</div>
                <div> {{ $store.getters.revenue.refund }} RSD</div>
            </div>
            <div class="flex flex-col px-10 py-5 border border-solid rounded drop-shadow-lg">
                <div class="font-bold">Zarada</div>
                <div> {{ $store.getters.revenue.income }} RSD</div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "OverviewStats",
}
</script>
<style scoped>
</style>
