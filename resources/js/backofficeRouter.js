import { createRouter, createWebHashHistory } from 'vue-router'
import Backoffice from './views/Backoffice.vue'
import BackofficeOverview from './components/Backoffice/BackofficeOverview.vue'
import BackofficeCategories from './components/Backoffice/BackofficeCategories.vue'
import BackofficeCategoriesNew from './components/Backoffice/BackofficeCategoriesNew.vue'
import BackofficeInventory from './components/Backoffice/BackofficeInventory.vue'
import BackofficeInventoryNew from './components/Backoffice/BackofficeInventoryNew.vue'
import BackofficeUsers from './components/Backoffice/BackofficeUsers.vue'
import BackofficeConnectionsLog from './components/Backoffice/BackofficeConnectionsLog.vue'
import BackofficeSettings from './components/Backoffice/BackofficeSettings.vue'
import BackofficeTables from './components/Backoffice/BackofficeTables.vue'
import BackofficeOrders from './components/Backoffice/BackofficeOrders.vue'
import BackofficeTasks from './components/Backoffice/BackofficeTasks.vue'
import BackofficeInvoices from './components/Backoffice/BackofficeInvoices.vue'
import BackofficeReports from './components/Backoffice/Reports/BackofficeReports.vue'
import BackofficeClients from './components/Backoffice/BackofficeClients.vue'
import BackofficeClientsNew from './components/Backoffice/BackofficeClientsNew.vue'
import BackofficeArrivals from './components/Backoffice/BackofficeArrivals.vue'
import BackofficeImportedSales from './components/Backoffice/Reports/BackofficeImportedSales.vue'
import BackofficeWarehouse from './components/Backoffice/Warehouse/BackofficeWarehouse.vue'
import BankInvoices from './components/Backoffice/BankInvoices/BankInvoices.vue'
import BackofficeWarehouseNew from "@/js/components/Backoffice/Warehouse/BackofficeWarehouseNew.vue";

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
                path: '/sales-imports',
                name: 'sales-imports',
                component: BackofficeImportedSales
            },
            {
                path: '/tasks',
                name: 'tasks',
                component: BackofficeTasks
            },
            {
                path: '/users',
                name: 'users',
                component: BackofficeUsers
            },
            {
                path: '/clients',
                name: 'clients',
                component: BackofficeClients
            },
            {
                path: '/bank-invoices',
                name: 'bank-invoices',
                component: BankInvoices
            },
            {
                path: '/arrivals',
                name: 'arrivals',
                component: BackofficeArrivals
            },
            {
                path: '/clients/new',
                name: 'clients-new',
                component: BackofficeClientsNew
            },
            {
                path: '/warehouse',
                name: 'warehouse',
                component: BackofficeWarehouse
            },
            {
                path: '/warehouse/new',
                name: 'warehouse-new',
                component: BackofficeWarehouseNew
            },
            {
                path: '/settings',
                name: 'settings',
                component: BackofficeSettings
            },
            {
                path: '/connection-logs',
                name: 'connection-logs',
                component: BackofficeConnectionsLog
            },
        ]
    },

]


const backofficeRouter = createRouter({
    history: createWebHashHistory(),
    routes
})

export default backofficeRouter
