<template>
  <div class="grid grid-cols-1 gap-8 lg:w-2/3">
    <BackofficeBackup />
    <BackofficeUsers />
    <BackofficeMyAccount />
  </div>
</template>

<script>
  import BackofficeBackup from './BackofficeBackup.vue'
  import BackofficeMyAccount from './BackofficeMyAccount.vue'
  import BackofficeUsers from './BackofficeUsers.vue'

  export default {
  components: { BackofficeMyAccount, BackofficeBackup, BackofficeUsers },

  }
</script>
