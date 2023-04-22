<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">Rezervna kopija baze podataka</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Pravi rezervnu kopiju i preuzima kompletnu trenutnu SQL Server bazu podataka.</p>
          </div>
        </div>
        <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:flex sm:items-center">
          <a href="/api/backoffice/db-backup" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">Preuzmi</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>
