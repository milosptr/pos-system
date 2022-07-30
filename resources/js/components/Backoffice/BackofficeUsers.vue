<template>
  <div class="flex flex-col">
      <div class="-my-0 -mx-0  sm:-mx-6 lg:-mx-8 w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Username</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Updated at</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell"></th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-for="(item, idx) in users" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="[{'bg-gray-50': idx % 2 === 1}]">
                  <td :class="[idx !== users.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ item.id }}</td>
                  <td :class="[idx !== users.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ']">{{ item.name }}</td>
                  <td :class="[idx !== users.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.username }}</td>
                  <td :class="[idx !== users.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.updated_at }}</td>
                  <td :class="[idx !== users.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
                    <div class="flex gap-5 text-right justify-end">
                      <div class="text-blue-500" @click="logoutUser(item.id)">Logout</div>
                      <div class="text-red-500" @click="deleteUser(item.id)">Delete</div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</template>
<script>
export default {
    name: 'BackofficeUsers',
    computed: {
      users() {
        return this.$store.getters.users
      }
    },
    mounted() {
        this.$store.dispatch('getUsers')
    },
    methods: {
      logoutUser(id) {

      },
      deleteUser(id) {
        axios.delete('/api/backoffice/users/' + id)
          .then(() => {
            this.$store.dispatch('getUsers')
          })
      },
    }
}
</script>
<style scoped>

</style>
