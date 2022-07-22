<template>
  <div class="">
    <div class="text-center text-4xl font-semibold mb-6">Unesite Pin</div>
    <div class="grid grid-cols-4 gap-3 NumpadWidth mx-auto">
      <div
        v-for="(p,i) in pin"
        :key="i"
        class="border border-gray-300 bg-gray-50 rounded-sm flex items-center justify-center w-full aspect-w-1 aspect-h-1"
        :class="{'border-red-500 error' : status === false}"
      >
        <div class="text-5xl font-semibold text-center leading-none pt-10">{{ p !== '' ? '*' : '' }}</div>
      </div>
    </div>
    <div class="grid grid-cols-3 gap-3 NumpadWidth mx-auto mt-10">
      <div
        v-for="key in keys"
        :key="key.key"
        class="border border-gray-300 rounded-sm flex items-center justify-center w-full aspect-w-3 aspect-h-2"
        :class="[key.background, key.color]"
        @click="enterKey(key.key)"
       >
        <div class="text-4xl font-semibold text-center leading-none pt-8">
          {{ key.key }}
        </div>
       </div>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      pin: ['', '', '', ''],
      status: null,
      pinIndex: 0,
      keys: [
        { key: 1, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 2, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 3, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 4, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 5, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 6, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 7, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 8, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 9, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 'Obriši', background: 'bg-red-500', color: 'text-white' },
        { key: 0, background: 'bg-gray-300', color: 'text-gray-900' },
        { key: 'Enter', background: 'bg-green-600', color: 'text-white'},
      ]
    }),
    methods: {
      enterKey(key) {
        this.status = null
        if(key === 'Enter') {
          if(!this.pin.some((p) => p === '')) {
            axios.post('/api/validate-pin', { pin: parseInt(this.pin.join(''))})
              .then((res) => {
                this.status = res.data.status
                if(this.status) this.$emit('success')
              })
          }
          return
        }
        if(key === 'Obriši' && this.pinIndex > 0) {
          this.pinIndex--
          this.pin[this.pinIndex] = ''
          return
        }

        if(this.pinIndex < 4 && key !== 'Enter' && key !== 'Obriši') {
          this.pin[this.pinIndex] = key
          this.pinIndex++
        }
      }
    }
  }
</script>

<style scoped>
  .error {
    animation: ErrorShake 0.2s ease-in-out;
  }

  .NumpadWidth {
    width: 90%;
  }

  @keyframes ErrorShake {
    0% {
      transform: translateX(0);
    }
    25% {
      transform: translateX(-10px);
    }
    50% {
      transform: translateX(0);
    }
    75% {
      transform: translateX(10px);
    }
    100% {
      transform: translateX(0);
    }
  }
</style>
