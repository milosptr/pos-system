<template>
  <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-[100]">
    <div class="absolute left-0 top-0 w-full h-screen bg-black bg-opacity-50" @click="$emit('close')"></div>
    <div class="bg-white md:w-1/2 rounded-md px-4 py-6 z-[101]">
      <div class="font-semibold text-gray-900">Dodaj PIB-ove dobavljačima</div>
      <p class="mt-1 text-sm text-gray-500">
        Jedan dobavljač po redu, u formatu <span class="font-mono">naziv:PIB</span>. Naziv ne mora biti ceo,
        dovoljan je deo koji odgovara samo jednom dobavljaču.
      </p>

      <div class="mt-4">
        <textarea
          v-model="lines"
          rows="8"
          spellcheck="false"
          placeholder="eps:100001378&#10;jksp:100578809"
          class="block w-full rounded-md border-0 py-1.5 font-mono text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600"
        />
      </div>

      <div v-if="error" class="mt-4 text-sm text-red-600">Greška pri čuvanju, pokušaj ponovo.</div>

      <div v-if="results.length" class="mt-4 max-h-60 overflow-y-auto border-t border-gray-200 pt-3">
        <div v-for="(result, idx) in results" :key="idx" class="py-1 text-sm">
          <div class="flex items-start gap-2">
            <span class="font-mono text-gray-400 shrink-0">{{ result.line }}</span>
            <span :class="statusClass(result.status)">{{ statusText(result) }}</span>
          </div>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end gap-3">
        <div @click="$emit('close')" class="cursor-pointer px-4 py-2 border border-gray-400 shadow-sm text-sm font-medium rounded-md text-gray-900 bg-white hover:bg-gray-100">Zatvori</div>
        <div @click="submit" class="cursor-pointer px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
          {{ saving ? 'Čuvam...' : 'Sačuvaj' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  const STATUS_TEXT = {
    updated: (r) => `PIB dodat: ${r.name}`,
    unchanged: (r) => `Već ima taj PIB: ${r.name}`,
    has_different_pib: (r) => `${r.name} već ima PIB ${r.current_pib}, nije promenjen`,
    pib_taken: (r) => `Taj PIB već koristi ${r.taken_by}, ${r.name} nije promenjen`,
    not_found: () => 'Dobavljač nije pronađen',
    ambiguous: (r) => `Odgovara više dobavljača: ${r.candidates.join(', ')}`,
    invalid_pib: () => 'PIB mora imati 9 cifara',
    invalid_line: () => 'Red mora biti u formatu naziv:PIB',
  }

  const WARNING_STATUSES = ['unchanged', 'has_different_pib', 'pib_taken']

  export default {
    data: () => ({
      lines: '',
      results: [],
      error: false,
      saving: false,
    }),
    methods: {
      statusText(result) {
        return STATUS_TEXT[result.status](result)
      },
      statusClass(status) {
        if (status === 'updated') {
          return 'text-green-700'
        }
        return WARNING_STATUSES.includes(status) ? 'text-amber-600' : 'text-red-600'
      },
      submit() {
        if (this.saving || !this.lines.trim()) {
          return
        }
        this.saving = true
        this.error = false
        axios.post('/api/bank-accounts/pibs', { lines: this.lines })
          .then((response) => {
            this.results = response.data.results
            if (response.data.applied > 0) {
              this.$emit('pibsAssigned')
            }
          })
          .catch((error) => {
            this.error = true
            this.results = []
            console.error('Failed to assign PIBs: ', error)
          })
          .finally(() => {
            this.saving = false
          })
      },
    },
  }
</script>
