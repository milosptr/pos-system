<template>
 <div class="mt-5">
    <div class="shadow-sm ring-1 ring-black ring-opacity-5">
      <table class="min-w-full border-separate" style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">ID</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Name</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Start at</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">End at</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Duration</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-for="(item, idx) in logs" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="[{'bg-gray-50': idx % 2 === 1}]">
            <td :class="[idx !== logs.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-gray-500 ']">{{ item.id }}</td>
            <td :class="[idx !== logs.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-gray-500 ']">Connection lost</td>
            <td :class="[idx !== logs.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-gray-800 font-medium tracking-wide']">{{ parseTimestamp(item.start_at) }}</td>
            <td :class="[idx !== logs.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-gray-800 font-medium tracking-wide']">{{ parseTimestamp(item.end_at) }}</td>
            <td :class="[idx !== logs.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-red-500 font-medium tracking-wide']">{{ parseDuration(item.start_at, item.end_at) }}s</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>

export default {
    name: 'BackofficeConnectionsLog',
    data: () => ({
      logs: {},
    }),
    computed: {},
    mounted() {
      axios.get('/api/backoffice/connections-log')
        .then((res) => {
          this.logs = res.data
        })
    },
    methods: {
      parseTimestamp(datetime) {
        return dayjs(datetime).subtract(10, 'second').format('MMMM D, YYYY HH:mm:ss')
      },
      parseDuration(start, end) {
        return dayjs(end).diff(dayjs(start), 'second', true)
      }
    }
}
</script>
<style scoped>

</style>
