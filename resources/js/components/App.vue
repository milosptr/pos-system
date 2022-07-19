<template>
  <div v-if="activeArea" class="relative w-full h-screen bg-repeat" :style="'background-image: url(' + activeArea?.pattern + '); height: 100vh; background-size: 35%;'">
    <Tables />
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
            v-for="tab in tabs"
            :key="tab.id"
            class="rounded-md bg-primary text-white text-xl py-5 px-6 flex items-center gap-3"
          >
            <img :src="tab.icon" alt="icon" width="28" />
            <div class="uppercase w-full tracking-wide font-medium">{{ tab.name }}</div>
          </div>
        </div>
        <div class="flex flex-col text-lg font-bold text-white text-center w-72">
          <div>Radni dan</div>
          <div>{{ timestamp }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Tables from './Tables/Tables.vue'

  export default {
    components: {
      Tables,
    },
    data: () => ({
      areas: [
        {id: 0, name: 'Sala', pattern: '/images/sala.jpg'},
        {id: 1, name: 'Basta', pattern: '/images/basta.jpg'},
      ],
      tabs: [
        {id: 0, name: 'Promet', icon: '/images/coins.svg'},
        {id: 1, name: 'Racuni', icon: '/images/receipt.svg'},
      ],
      activeArea: null,
      timestamp: null
    }),
    computed: {
    },
    mounted() {
      this.activeArea = this.areas[0]
      setInterval(() => {
        this.timestamp = this.getDate()
      }, 1000);
    },
    methods: {
      selectActiveArea(id) {
        this.activeArea = this.areas.find((a) => a.id === id)
      },
      getDate() {
        const now = new Date()
        const date = now.getDate() + '.' + (now.getMonth() + 1) + '.' + now.getFullYear()
        const hours = now.getHours() < 10 ? '0' + now.getHours() : now.getHours()
        const minutes = now.getMinutes() < 10 ? '0' + now.getMinutes() : now.getMinutes()
        const seconds = now.getSeconds() < 10 ? '0' + now.getSeconds() : now.getSeconds()
        const time = hours + ":" + minutes + ":" + seconds;
        return date + ' ' + time
      }
    }
  }
</script>
