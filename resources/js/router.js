import { createRouter, createWebHashHistory } from 'vue-router'
import App from './views/App.vue'
import Transactions from './views/Transactions.vue'
import Invoices from './views/Invoices.vue'
import SidebarLayout from './views/SidebarLayout.vue'
import Table from './components/Tables/Table.vue'
import InventoryOverview from './components/InventoryOverview/InventoryOverview.vue'

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
          default: InventoryOverview,
          sidebar: Table,
        }
      }
    ]
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: Transactions,
  },
  {
    path: '/invoices',
    name: 'invoices',
    component: Invoices,
  },

]
const router = createRouter({
  history: createWebHashHistory(),
  routes
})
export default router
