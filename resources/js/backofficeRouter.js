import { createRouter, createWebHashHistory } from 'vue-router'
import Backoffice from './views/Backoffice.vue'
import BackofficeOverview from './components/Backoffice/BackofficeOverview.vue'
import BackofficeCategories from './components/Backoffice/BackofficeCategories.vue'
import BackofficeCategoriesNew from './components/Backoffice/BackofficeCategoriesNew.vue'
import BackofficeInventory from './components/Backoffice/BackofficeInventory.vue'
import BackofficeInventoryNew from './components/Backoffice/BackofficeInventoryNew.vue'
import BackofficeUsers from './components/Backoffice/BackofficeUsers.vue'
import BackofficeTables from './components/Backoffice/BackofficeTables.vue'
import BackofficeOrders from './components/Backoffice/BackofficeOrders.vue'
import BackofficeInvoices from './components/Backoffice/BackofficeInvoices.vue'
import BackofficeReports from './components/Backoffice/Reports/BackofficeReports.vue'

const routes = [
    {
        path: '/',
        component: Backoffice,
        children: [
            {
                path: '/',
                name: 'overview',
                component: BackofficeOverview
            },
            {
                path: '/categories',
                name: 'categories',
                component: BackofficeCategories,
            },
            {
                path: '/categories/new',
                name: 'categories-new',
                component: BackofficeCategoriesNew,
            },
            {
                path: '/inventory',
                name: 'inventory',
                component: BackofficeInventory,
            },
            {
              path: '/inventory/new',
              name: 'inventory-new',
              component: BackofficeInventoryNew,
            },
            {
                path: '/users',
                name: 'users',
                component: BackofficeInventoryNew
            },
            {
                path: '/tables',
                name: 'tables',
                component: BackofficeTables
            },
            {
                path: '/orders',
                name: 'orders',
                component: BackofficeOrders
            },
            {
                path: '/invoices',
                name: 'invoices',
                component: BackofficeInvoices
            },
            {
                path: '/settings',
                name: 'settings',
                component: BackofficeInvoices
            },
            {
                path: '/reports',
                name: 'reports',
                component: BackofficeReports
            },
            {
                path: '/users',
                name: 'users',
                component: BackofficeUsers
            },
        ]
    },

]


const backofficeRouter = createRouter({
    history: createWebHashHistory(),
    routes
})

export default backofficeRouter
