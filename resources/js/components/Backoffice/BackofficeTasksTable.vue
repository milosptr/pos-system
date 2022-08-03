<template>
  <div class="flex flex-col">
    <div class="w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
      <div class="inline-block min-w-full py-2 align-middle">
        <div class="shadow-sm ring-1 ring-black ring-opacity-5">
          <table class="min-w-full border-separate" style="border-spacing: 0">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="sticky top-0 w-20 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Company</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Date</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Price</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Message</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Status</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <BackofficeTasksTableItem
                v-for="(item, idx) in tasks"
                :key="item.id"
                :item="item"
                :tasks="tasks"
                :idx="idx"
                :class="{'bg-gray-50': idx % 2 === 1 }"
              />
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import BackofficeTasksTableItem from './BackofficeTasksTableItem.vue'
  export default {
    components: { BackofficeTasksTableItem },
    computed: {
      tasks() {
        return this.$store.getters.tasks
      }
    }
  }
</script>
