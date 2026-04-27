import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import pos from './pos'
import transactions from './transactions'
import products from './products'
import customers from './customers'
import angsuran from './angsuran'
import konversiStok from './konversi-stok'
import goodsIn from './goods-in'
import goodsInReceiving from './goods-in-receiving'
import stockAdjustment from './stock-adjustment'
import profile from './profile'
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/kasir',
} satisfies RouteDefinition<["get","head"]>

dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

        const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

                    dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
                    dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
const kasir = {
    dashboard: Object.assign(dashboard, dashboard),
pos: Object.assign(pos, pos),
transactions: Object.assign(transactions, transactions),
products: Object.assign(products, products),
customers: Object.assign(customers, customers),
angsuran: Object.assign(angsuran, angsuran),
konversiStok: Object.assign(konversiStok, konversiStok),
goodsIn: Object.assign(goodsIn, goodsIn),
goodsInReceiving: Object.assign(goodsInReceiving, goodsInReceiving),
stockAdjustment: Object.assign(stockAdjustment, stockAdjustment),
profile: Object.assign(profile, profile),
}

export default kasir