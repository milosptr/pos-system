<template>
  <div class="">
    <div class="mb-6 text-center text-4xl font-semibold">Unesite Pin</div>
    <div class="NumpadWidth mx-auto grid grid-cols-4 gap-3">
      <div
        v-for="(p, i) in pin"
        :key="i"
        class="SingleKey PinBoxes flex w-full items-center justify-center rounded-sm border border-gray-300 bg-gray-50"
        :class="{ 'error border-red-500': status === false }">
        <div class="text-center text-5xl font-semibold leading-none">{{ p !== '' ? '＊' : '' }}</div>
      </div>
    </div>
    <div class="NumpadWidth mx-auto mt-10 grid grid-cols-3 gap-3">
      <div
        v-for="key in keys"
        :key="key.key"
        class="SingleKey flex w-full items-center justify-center rounded-sm border border-gray-300"
        :class="[key.background, key.color, { KeyButton: key.isButton }]"
        @click="enterKey(key.key)">
        <div class="text-center text-4xl font-semibold leading-none">
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
      { key: 'Obriši', background: 'bg-red-500', color: 'text-white', isButton: true },
      { key: 0, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 'Enter', background: 'bg-green-600', color: 'text-white', isButton: true }
    ]
  }),
  methods: {
    enterKey(key) {
      this.status = null
      if (key === 'Enter') {
        if (!this.pin.some((p) => p === '')) {
          axios.post('/api/validate-pin', { pin: parseInt(this.pin.join('')) }).then((res) => {
            this.status = res.data.status
            if (this.status) this.$emit('success')
          })
        }
        return
      }
      if (key === 'Obriši' && this.pinIndex > 0) {
        this.pinIndex--
        this.pin[this.pinIndex] = ''
        return
      }

      if (this.pinIndex < 4 && key !== 'Enter' && key !== 'Obriši') {
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

.SingleKey {
  height: 70px;
}

.KeyButton div {
  font-size: 26px;
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
