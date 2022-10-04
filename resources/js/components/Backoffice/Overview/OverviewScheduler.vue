<template>
  <div>
    <div class="text-xl font-semibold mt-10">Employees</div>
    <div class="mt-5 bg-white shadow px-4 py-5 sm:p-6">
      <div class="text-lg font-semibold">Kuhinja</div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 mt-2">
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">I</div>
          <div class="grid gap-2 p-4">
            <div
              v-for="schedule in getFilteredEmployees(1,1)"
              :key="schedule.id"
              class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
          </div>
        </div>
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">M</div>
          <div class="grid gap-2 p-4">
              <div
                v-for="schedule in getFilteredEmployees(2,1)"
                :key="schedule.id"
                class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
                :style="'background: ' + schedule.employee.color"
              >
                {{ schedule.employee.name }}
                <span v-if="schedule.time"> ({{ schedule.time }})</span>
              </div>
            </div>
        </div>
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">II</div>
          <div class="grid gap-2 p-4">
            <div
              v-for="schedule in getFilteredEmployees(3,1)"
              :key="schedule.id"
              class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5 bg-white shadow px-4 py-5 sm:p-6">
      <div class="text-lg font-semibold">Šank</div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 mt-2">
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">I</div>
          <div class="grid gap-2 p-4">
            <div
              v-for="schedule in getFilteredEmployees(1,0)"
              :key="schedule.id"
              class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
          </div>
        </div>
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">M</div>
          <div class="grid gap-2 p-4">
              <div
                v-for="schedule in getFilteredEmployees(2,0)"
                :key="schedule.id"
                class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
                :style="'background: ' + schedule.employee.color"
              >
                {{ schedule.employee.name }}
                <span v-if="schedule.time"> ({{ schedule.time }})</span>
              </div>
            </div>
        </div>
        <div class="text-center">
          <div class="border border-gray-200 bg-gray-100 font-bold">II</div>
          <div class="grid gap-2 p-4">
            <div
              v-for="schedule in getFilteredEmployees(3,0)"
              :key="schedule.id"
              class="relative rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      schedules: [],
    }),
    mounted() {
      fetch("http://192.168.200.30:81/public/today")
        .then(response => response.json())
        .then(result => { this.schedules = result.data })
        .catch(error => console.log('error', error))
    },
    methods: {
      getFilteredEmployees(shift, occupation) {
        return this.schedules.filter((s) => s.shift === shift && s.occupation === occupation)
      }
    }
  }
</script>
