<template>
  <div>
    <div class="flex flex-col md:flex-row md:items-end gap-4">
      <div class="w-full md:w-72">
        <label for="supplier" class="block text-sm font-medium leading-6 text-gray-900">Dobavljač</label>
        <select id="supplier" v-model="clientAccount" name="supplier" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
          <option :value="null">Izaberi dobavljača</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
        </select>
      </div>
      <div class="w-full md:w-64">
        <label for="search" class="block text-sm font-medium leading-6 text-gray-900">Artikal</label>
        <input id="search" v-model="search" type="text" name="search" placeholder="Traži po nazivu" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
      </div>
      <label class="flex items-center gap-2 md:pb-2 text-sm text-gray-900">
        <input v-model="changedOnly" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
        Samo promenjene cene
      </label>
    </div>

    <p class="mt-4 text-xs text-gray-400">Gornji broj je cena bez PDV-a, donji sa PDV-om.</p>

    <div class="mx-auto mt-2">
      <div v-if="!clientAccount" class="bg-white border-2 border-gray-200 rounded-md px-4 py-6 text-sm text-gray-400">
        Izaberi dobavljača da vidiš kako su se cene menjale.
      </div>
      <div v-else-if="loading" class="bg-white border-2 border-gray-200 rounded-md px-4 py-6 text-sm text-gray-400">
        Učitavanje cena...
      </div>
      <div v-else-if="error" class="bg-white border-2 border-gray-200 rounded-md px-4 py-6 text-sm text-red-600">
        Greška pri učitavanju cena.
        <span class="underline cursor-pointer" @click="fetchArticles">Pokušaj ponovo</span>
      </div>
      <div v-else-if="!articles.length" class="bg-white border-2 border-gray-200 rounded-md px-4 py-6 text-sm text-gray-400">
        Nema sačuvanih cena za ovog dobavljača.
      </div>
      <table v-else class="w-full text-left bg-white border-2 border-gray-200">
        <thead>
          <tr class="text-xs text-gray-400 bg-gray-50 border-b border-gray-200">
            <th class="font-medium py-2 px-4">Artikal</th>
            <th class="font-medium py-2 px-4">JM</th>
            <th class="font-medium py-2 px-4">Poslednja</th>
            <th class="font-medium py-2 px-4">Ranije</th>
          </tr>
        </thead>
        <tbody>
          <SupplierPriceRow v-for="article in articles" :key="article.name + article.unit" :article="article" />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
  import SupplierPriceRow from './SupplierPriceRow.vue'

  const SEARCH_DEBOUNCE_MS = 300

  export default {
    components: {
      SupplierPriceRow,
    },
    data: () => ({
      bankAccounts: [],
      clientAccount: null,
      search: '',
      changedOnly: false,
      articles: [],
      loading: false,
      error: false,
    }),
    computed: {
      clients() {
        return [...this.bankAccounts].sort((a, b) => a.name.localeCompare(b.name))
      },
    },
    watch: {
      clientAccount() {
        this.fetchArticles()
      },
      changedOnly() {
        this.fetchArticles()
      },
      search() {
        this.debouncedFetch()
      },
    },
    created() {
      this.debouncedFetch = _.debounce(this.fetchArticles, SEARCH_DEBOUNCE_MS)
    },
    mounted() {
      this.fetchBankAccounts()
    },
    methods: {
      fetchBankAccounts() {
        axios.get('/api/bank-accounts')
          .then((response) => {
            this.bankAccounts = response.data
          })
          .catch((error) => {
            console.error('Failed to load suppliers: ', error)
          })
      },
      fetchArticles() {
        if (!this.clientAccount) {
          this.articles = []
          return
        }

        const params = { client_account: this.clientAccount }
        if (this.search.trim()) {
          params.search = this.search.trim()
        }
        if (this.changedOnly) {
          params.changed = 1
        }

        this.loading = true
        this.error = false
        axios.get('/api/supplier-prices', { params })
          .then((response) => {
            this.articles = response.data.articles
          })
          .catch((error) => {
            // Never let a failed request read as "this supplier has no prices".
            this.error = true
            this.articles = []
            console.error('Failed to load supplier prices: ', error)
          })
          .finally(() => {
            this.loading = false
          })
      },
    },
  }
</script>
