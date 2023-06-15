<template>
  <div class="shadow sm:overflow-hidden sm:rounded-md">
    <div class="bg-white px-4 py-6 sm:p-6">
      <h2
        id="payment-details-heading"
        class="text-lg font-medium leading-6 text-gray-900">
        Korisnici/Zaposleni
      </h2>
      <table class="mt-5 min-w-full divide-y divide-gray-300">
        <thead>
          <tr>
            <th
              scope="col"
              class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
              ID
            </th>
            <th
              scope="col"
              class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
              Ime
            </th>
            <th
              scope="col"
              class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
              Korisničko ime
            </th>
            <th
              scope="col"
              class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
              Poslednja promena
            </th>
            <th
              scope="col"
              class="relative py-3.5 pl-3 pr-4 sm:pr-6 md:pr-0">
              <span class="sr-only">Edit</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="(item, idx) in users"
            :key="item.id"
            class="cursor-pointer hover:bg-orange-50"
            :class="[{ 'bg-gray-50': idx % 2 === 1 }]">
            <td
              :class="[
                idx !== users.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900'
              ]">
              {{ item.id }}
            </td>
            <td
              :class="[
                idx !== users.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
              ]">
              {{ item.name }}
            </td>
            <td
              :class="[
                idx !== users.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              {{ item.username }}
            </td>
            <td
              :class="[
                idx !== users.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              {{ item.updated_at }}
            </td>
            <td
              :class="[
                idx !== users.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              <div class="flex justify-end gap-5 text-right">
                <div
                  class="text-red-500"
                  @click="deleteUser(item.id)">
                  Delete
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
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
    logoutUser(id) {},
    deleteUser(id) {
      axios.delete('/api/backoffice/users/' + id).then(() => {
        this.$store.dispatch('getUsers')
      })
    }
  }
}
</script>
<style scoped></style>
