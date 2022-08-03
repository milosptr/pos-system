<template>
  <div class="fixed bottom-0 left-0 w-full z-10" style="background: rgb(32,	73,	101)">
    <div class="flex items-center justify-between">
      <div class="flex gap-5 px-6 my-3 w-72">
        <div
          v-for="area in areas"
          :key="area.id"
          class="w-1/2 p-2 rounded-md"
          style="background: rgb(65,	154,	174)"
          @click="selectActiveArea(area.id)"
        >
          <div class="text-center text-white text-xl uppercase font-medium">
            {{ area.name }}
          </div>
          <div class="h-10 w-full" :style="'background-image: url('+ area.pattern + '); background-size: 80%;'"></div>
        </div>
      </div>
      <div class="flex gap-5">
        <div
          class="rounded-md bg-primary text-white text-xl py-5 px-6 flex items-center gap-3"
          @click="showTransactionsModal = true"
        >
          <img :src="$filters.imgUrl('coins.svg')" alt="icon" width="28" />
          <div class="uppercase w-full tracking-wide font-medium">Promet</div>
        </div>
        <div
          class="rounded-md bg-primary text-white text-xl py-5 px-6 flex items-center gap-3"
           @click="showInvoicesModal = true"
        >
          <img :src="$filters.imgUrl('receipt.svg')" alt="icon" width="28" />
          <div class="uppercase w-full tracking-wide font-medium">Racuni</div>
        </div>
      </div>
      <div class="flex flex-col text-lg font-bold text-white text-center w-72" @click="toggleFullScreen">
        <div>Radni dan</div>
        <div>{{ timestamp }}</div>
      </div>
    </div>
    <InvoicesModal v-if="showInvoicesModal" @close="showInvoicesModal = false" />
    <TransactionsModal v-if="showTransactionsModal" @close="showTransactionsModal = false" />
  </div>
</template>

<script>
  import TransactionsModal from './Modals/TransactionsModal.vue'
  import InvoicesModal from './Modals/InvoicesModal.vue'

  export default {
  components: { TransactionsModal, InvoicesModal },
    data: () => ({
      timestamp: null,
      showTransactionsModal: false,
      showInvoicesModal: false,
      tabs: [
        {id: 0, name: 'Promet', url: '/transactions', icon: '/images/coins.svg'},
        {id: 1, name: 'Racuni', url: '/invoices', icon: '/images/receipt.svg'},
      ],
    }),
    computed: {
      activeArea() {
        return this.$store.getters.getActiveArea
      },
      areas() {
        return this.$store.getters.getAreas
      },
    },
    mounted() {
      this.timestamp = this.getDate()
      setInterval(() => {
        this.timestamp = this.getDate()
      }, 1000);
    },
    methods: {
      selectActiveArea(id) {
       this.$store.dispatch('storeActiveArea', this.areas.find((a) => a.id === id))
       this.$router.push('/')
      },
      getDate() {
        const now = new Date()
        const date = now.getDate() + '.' + (now.getMonth() + 1) + '.' + now.getFullYear()
        const hours = now.getHours() < 10 ? '0' + now.getHours() : now.getHours()
        const minutes = now.getMinutes() < 10 ? '0' + now.getMinutes() : now.getMinutes()
        const seconds = now.getSeconds() < 10 ? '0' + now.getSeconds() : now.getSeconds()
        const time = hours + ":" + minutes + ":" + seconds;
        return date + ' ' + time
      },
      toggleFullScreen() {
        var doc = window.document;
        var docEl = doc.documentElement;

        var requestFullScreen = docEl.requestFullscreen || docEl.mozRequestFullScreen || docEl.webkitRequestFullScreen || docEl.msRequestFullscreen;
        var cancelFullScreen = doc.exitFullscreen || doc.mozCancelFullScreen || doc.webkitExitFullscreen || doc.msExitFullscreen;

        if(!doc.fullscreenElement && !doc.mozFullScreenElement && !doc.webkitFullscreenElement && !doc.msFullscreenElement) {
        requestFullScreen.call(docEl);
        }
        else {
        cancelFullScreen.call(doc);
        }
      }
    }
  }
</script>
