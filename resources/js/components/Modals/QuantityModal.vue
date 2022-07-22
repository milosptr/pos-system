<template>
  <Modal :numpad="true">
      <Numpad />
  </Modal>
</template>

<script>
  import Modal from '../Modals/Modal.vue'
  import Numpad from '../Numpad.vue'

  export default {
  components: { Modal, Numpad },

  }
</script>
