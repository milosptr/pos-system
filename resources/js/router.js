import { createRouter, createWebHashHistory } from 'vue-router'
import App from './views/App.vue'
import SidebarLayout from './views/SidebarLayout.vue'
import Table from './components/Tables/Table.vue'
import InventoryOverview from './components/InventoryOverview/InventoryOverview.vue'
import InvoicesOverview from './components/Invoices/InvoicesOverview.vue'
import InvoicesSidebar from './components/Invoices/InvoicesSidebar.vue'

const routes = [
  {
    path: '/',
    name: 'app',
    component: App,
  },
  {
    path: '/table',
    name: 'table',
    component: SidebarLayout,
    children: [
      {
        path: ':id',
        components: {
          main: InventoryOverview,
          sidebar: Table,
        }
      }
    ]
  },
  {
    path: '/invoices',
    name: 'invoices',
    component: SidebarLayout,
    children: [
      {
        name: "invoice",
        path: '',
        components: {
          main: InvoicesOverview,
          sidebar: InvoicesSidebar
        }
      }
    ]
  },

]
const router = createRouter({
  history: createWebHashHistory(),
  routes
})
export default router
