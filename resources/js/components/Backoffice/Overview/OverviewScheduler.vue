<template>
  <div>
    <div class="mt-10 text-xl font-semibold">Radnici</div>
    <div class="mt-5 rounded-lg bg-white shadow">
      <div class="grid grid-cols-1 divide-x divide-y divide-gray-200 lg:grid-cols-3 lg:divide-y-0 xl:grid-cols-6">
        <div
          v-for="(shift, index) in shifts"
          :key="'s-' + shift"
          class="flex flex-col gap-2 p-4 sm:p-6 sm:pt-2">
          <div class="mb-3 border-b border-gray-200 text-center text-lg font-semibold">
            {{ shift }}
          </div>
          <div class="flex h-full w-full flex-col items-center justify-center gap-2.5">
            <div
              v-for="schedule in getFilteredEmployees(index + 1, 1)"
              :key="schedule.id"
              class="relative w-full select-none rounded-xl px-1 py-1 text-center text-sm tracking-wide text-white lg:px-2"
              :style="`background: ${schedule.employee.color}; order: ${schedule.order}`">
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
              <div
                v-if="schedule.employee.lastCheckin"
                class="flex justify-center gap-2">
                <div v-if="schedule.employee.lastCheckin.check_in">
                  {{ formatDate(schedule.employee.lastCheckin.check_in) }}
                </div>
                <div>-</div>
                <div v-if="schedule.employee.lastCheckin.check_out">
                  {{ formatDate(schedule.employee.lastCheckin.check_out) }}
                </div>
              </div>
              <div
                v-if="schedule.from_checkin"
                className="absolute -top-1 -left-0.5 w-3 h-3 rounded-full bg-red-500 text-[8px] flex items-center justify-center">
                A
              </div>
              <div
                v-if="!(!!schedule.employee?.lastCheckin?.check_out || schedule.employee.lastCheckin === null)"
                class="absolute -right-1 -top-2.5 h-[16px] w-[16px] rounded-full border-2 border-solid border-white bg-[#0BDA51]" />
            </div>
          </div>
        </div>
        <div
          v-for="(shift, index) in shifts"
          :key="'s-' + shift"
          class="flex flex-col gap-2 p-4 sm:p-6 sm:pt-2">
          <div class="mb-3 border-b border-gray-200 text-center text-lg font-semibold">
            {{ shift }}
          </div>
          <div class="flex h-full w-full flex-col items-center justify-center gap-2.5">
            <div
              v-for="schedule in getFilteredEmployees(index + 1, 0)"
              :key="schedule.id"
              class="relative w-full select-none rounded-xl px-1 py-1 text-center text-sm tracking-wide text-white lg:px-2"
              :style="`background: ${schedule.employee.color}; order: ${schedule.order}`">
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
              <div
                v-if="schedule.employee.lastCheckin"
                class="flex justify-center gap-2">
                <div v-if="schedule.employee.lastCheckin.check_in">
                  {{ formatDate(schedule.employee.lastCheckin.check_in) }}
                </div>
                <div>-</div>
                <div v-if="schedule.employee.lastCheckin.check_out">
                  {{ formatDate(schedule.employee.lastCheckin.check_out) }}
                </div>
              </div>
              <div
                v-if="schedule.from_checkin"
                className="absolute -top-1 -left-0.5 w-3 h-3 rounded-full bg-red-500 text-[8px] flex items-center justify-center">
                A
              </div>
              <div
                v-if="!(!!schedule.employee?.lastCheckin?.check_out || schedule.employee.lastCheckin === null)"
                class="absolute -right-1 -top-2.5 h-[16px] w-[16px] rounded-full border-2 border-solid border-white bg-[#0BDA51]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const dateFormatOptions = {
  hour: 'numeric',
  minute: 'numeric',
  hourCycle: 'h23'
}

export default {
  data: () => ({
    shifts: ['I', 'M', 'II'],
    schedules: []
  }),
  mounted() {
    this.pusherInit()
    this.fetchSchedules()
  },
  methods: {
    pusherInit() {
      const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER
      })
      console.log(pusher)
      pusher.subscribe('broadcasting')
      pusher.bind('employee-checkin', (data) => {
        console.log('fetching new info')
        this.fetchSchedules()
      })
    },
    fetchSchedules() {
      // fetch("http://192.168.200.30:81/public/today")
      fetch('http://192.168.200.30:81/public/today')
        .then((response) => response.json())
        .then((result) => {
          this.schedules = result.data
        })
        .catch((error) => console.log('error', error))
    },
    getFilteredEmployees(shift, occupation) {
      return this.schedules.filter((s) => s.shift === shift && s.occupation === occupation)
    },
    formatDate(date) {
      if (date) {
        return new Intl.DateTimeFormat('default', dateFormatOptions).format(new Date(date))
      }
      return ''
    }
  }
}
</script>
