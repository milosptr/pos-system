<template>
  <div class="grid grid-cols-1 gap-8 lg:w-2/3">
    <BackofficeCheckinSettings />
    <BackofficeBackup />
    <BackofficeUsers />
    <BackofficeMyAccount />
  </div>
</template>

<script>
  import BackofficeCheckinSettings from './BackofficeCheckinSettings.vue'
  import BackofficeBackup from './BackofficeBackup.vue'
  import BackofficeMyAccount from './BackofficeMyAccount.vue'
  import BackofficeUsers from './BackofficeUsers.vue'

  export default {
  components: { BackofficeCheckinSettings, BackofficeMyAccount, BackofficeBackup, BackofficeUsers },

  }
</script>
