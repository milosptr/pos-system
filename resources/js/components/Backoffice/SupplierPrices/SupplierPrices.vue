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
      <div class="inline-flex rounded-md ring-1 ring-inset ring-gray-300 overflow-hidden">
        <div
          v-for="option in windowOptions"
          :key="option.months"
          class="cursor-pointer px-3 py-1.5 text-sm"
          :class="option.months === windowMonths ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50'"
          @click="windowMonths = option.months"
        >
          {{ option.label }}
        </div>
      </div>
    </div>

    <div v-if="articles.length" class="mt-4 flex flex-wrap items-center gap-2 text-sm">
      <span class="text-gray-500">{{ articles.length }} {{ articles.length === 1 ? 'artikal' : 'artikala' }}</span>
      <div
        v-for="chip in chips"
        :key="chip.key"
        class="cursor-pointer rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
        :class="chip.key === filter ? chip.activeClass : 'text-gray-500 bg-white ring-gray-300 hover:bg-gray-50'"
        @click="filter = chip.key === filter ? null : chip.key"
      >
        {{ chip.label }}
      </div>
    </div>

    <div class="mx-auto mt-3">
      <div v-if="!clientAccount" class="bg-white ring-1 ring-gray-200 rounded-lg px-4 py-6 text-sm text-gray-400">
        Izaberi dobavljača da vidiš kako su se cene menjale.
      </div>
      <div v-else-if="loading" class="bg-white ring-1 ring-gray-200 rounded-lg divide-y divide-gray-100">
        <div v-for="skeleton in SKELETON_ROWS" :key="skeleton" class="h-11 px-4 flex items-center gap-4">
          <div class="h-3 w-48 rounded bg-gray-100 animate-pulse" />
          <div class="h-1.5 flex-1 rounded bg-gray-100 animate-pulse" />
          <div class="h-3 w-16 rounded bg-gray-100 animate-pulse" />
        </div>
      </div>
      <div v-else-if="error" class="bg-white ring-1 ring-gray-200 rounded-lg px-4 py-6 text-sm text-red-600">
        Greška pri učitavanju cena.
        <span class="underline cursor-pointer" @click="fetchArticles">Pokušaj ponovo</span>
      </div>
      <div v-else-if="!articles.length" class="bg-white ring-1 ring-gray-200 rounded-lg px-4 py-6 text-sm text-gray-400">
        {{ search.trim() ? `Nema artikala za „${search.trim()}“` : 'Nema sačuvanih cena za ovog dobavljača.' }}
      </div>
      <div v-else-if="!visibleArticles.length" class="bg-white ring-1 ring-gray-200 rounded-lg px-4 py-6 text-sm text-gray-400">
        Nema artikala u ovoj grupi.
      </div>
      <table v-else class="w-full text-left bg-white ring-1 ring-gray-200 rounded-lg overflow-hidden">
        <thead>
          <tr class="text-xs text-gray-400 bg-gray-50 border-b border-gray-200">
            <th class="font-medium py-2 px-4 w-64">Artikal</th>
            <th class="font-medium py-2 pr-4 border-r border-gray-300">
              <div class="relative h-4">
                <span v-for="tick in ticks" :key="tick.at" class="absolute top-0 leading-4" :style="{ left: tick.at + '%' }">{{ tick.label }}</span>
              </div>
            </th>
            <th class="font-medium py-2 px-4 text-right w-24">Trenutna</th>
            <th class="font-medium py-2 pr-4 text-right w-28">Promena</th>
            <th class="font-medium py-2 pr-4 w-8"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <SupplierPriceRow
            v-for="article in visibleArticles"
            :key="article.name + article.unit"
            :article="article"
            :window="priceWindow"
            :ticks="ticks"
          />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
  import SupplierPriceRow from './SupplierPriceRow.vue'

  const SEARCH_DEBOUNCE_MS = 300
  const SKELETON_ROWS = 6

  const MONTH_NAMES = ['jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'avg', 'sep', 'okt', 'nov', 'dec']

  const WINDOW_OPTIONS = [
    { months: 3, label: '3m' },
    { months: 6, label: '6m' },
    { months: 12, label: '12m' },
    { months: 24, label: 'Sve' },
  ]

  // Below this the month labels start touching, so only every second or third
  // one is drawn.
  const WIDE_TICK_PERCENT = 9
  const NARROW_TICK_PERCENT = 5

  const CHIPS = [
    { key: 'up', label: '↑ poskupelo', activeClass: 'text-white bg-red-600 ring-red-600' },
    { key: 'down', label: '↓ pojeftinilo', activeClass: 'text-white bg-green-600 ring-green-600' },
    { key: 'flat', label: 'bez promene', activeClass: 'text-white bg-gray-600 ring-gray-600' },
  ]

  export default {
    components: {
      SupplierPriceRow,
    },
    data: () => ({
      bankAccounts: [],
      clientAccount: null,
      search: '',
      windowMonths: 12,
      filter: null,
      articles: [],
      loading: false,
      error: false,
      SKELETON_ROWS,
      windowOptions: WINDOW_OPTIONS,
    }),
    computed: {
      clients() {
        return [...this.bankAccounts].sort((a, b) => a.name.localeCompare(b.name))
      },
      // The window never reaches back further than the supplier's first
      // invoice, so a new supplier fills the width instead of huddling on the
      // right of an empty year.
      priceWindow() {
        const to = dayjs().endOf('day')
        const wanted = to.subtract(this.windowMonths, 'month')
        const oldest = this.articles.reduce((earliest, article) => {
          const first = dayjs(article.first.date)
          return !earliest || first.isBefore(earliest) ? first : earliest
        }, null)
        const from = oldest && oldest.isAfter(wanted) ? oldest.subtract(7, 'day') : wanted
        return { from: from.valueOf(), to: to.valueOf() }
      },
      ticks() {
        const from = dayjs(this.priceWindow.from)
        const span = this.priceWindow.to - this.priceWindow.from
        const months = []
        let cursor = from.startOf('month').add(1, 'month')

        while (cursor.valueOf() < this.priceWindow.to) {
          months.push(cursor)
          cursor = cursor.add(1, 'month')
        }

        const spacing = months.length > 1 ? 100 / months.length : 100
        const every = spacing >= WIDE_TICK_PERCENT ? 1 : (spacing >= NARROW_TICK_PERCENT ? 2 : 3)

        return months
          .filter((month, index) => index % every === 0)
          .map((month) => ({
            at: ((month.valueOf() - this.priceWindow.from) / span) * 100,
            label: month.month() === 0 ? `${MONTH_NAMES[0]} ${month.format('YY')}` : MONTH_NAMES[month.month()],
          }))
      },
      chips() {
        return CHIPS.map((chip) => ({
          ...chip,
          label: `${this.grouped[chip.key].length} ${chip.label}`,
        }))
      },
      grouped() {
        return {
          up: this.articles.filter((article) => this.movement(article) > 0),
          down: this.articles.filter((article) => this.movement(article) < 0),
          flat: this.articles.filter((article) => this.movement(article) === 0),
        }
      },
      visibleArticles() {
        const articles = this.filter ? this.grouped[this.filter] : this.articles
        return [...articles].sort((a, b) => Math.abs(this.movement(b)) - Math.abs(this.movement(a)))
      },
    },
    watch: {
      clientAccount() {
        this.filter = null
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
      movement(article) {
        return article.total_change || 0
      },
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
