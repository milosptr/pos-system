<template>
  <Modal :numpad="true">
    <EnterPin @success="success" />
  </Modal>
</template>

<script>
  import EnterPin from '../EnterPin.vue'
  import Modal from './Modal.vue'

  export default {
    components: {
      Modal,
      EnterPin,
    },
    methods: {
      success() {
        this.$router.push('/invoices')
      }
    }
  }
</script>
