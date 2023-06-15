<template>
  <Modal :numpad="true">
    <Numpad
      @input="handleValue"
      :subtitle="lastItemInOrder.name" />
  </Modal>
</template>

<script>
import Modal from '../Modals/Modal.vue'
import Numpad from '../Numpad.vue'

export default {
  components: { Modal, Numpad },
  data: () => ({}),
  computed: {
    lastItemInOrder() {
      return this.$store.getters.lastItemInOrder
    }
  },
  methods: {
    handleValue(v) {
      let qty = parseFloat(v)
      if (qty) {
        this.$store.commit('setLastOrderCustomQty', qty)
        this.$emit('close')
      }
    }
  }
}
</script>
