<template>
  <div
    v-click-outside="() => $emit('close')"
    class="absolute top-0 left-0 bg-white shadow-lg border border-gray-200 z-10 rounded-sm text-lg uppercase font-semibold">
    <div class="py-3 px-5 border-b border-gray-200" @click="handleClick('reprint')">
      Ponovno štampanje
    </div>
    <div class="py-3 px-5 border-b border-gray-200" @click="handleClick('cashout')">
      Naplati porudžbinu
    </div>
  </div>
</template>

<script>
  export default {
    methods: {
      handleClick(type) {
        this.$emit('selected', type)
        this.$emit('close')
      }
    }
  }
</script>
