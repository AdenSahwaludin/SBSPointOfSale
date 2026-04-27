import DashboardController from './DashboardController'
import TransaksiPOSController from './TransaksiPOSController'
import TransaksiController from './TransaksiController'
import ProdukController from './ProdukController'
import AngsuranController from './AngsuranController'
import KonversiStokController from './KonversiStokController'
import GoodsInController from './GoodsInController'
import StockAdjustmentController from './StockAdjustmentController'
const Kasir = {
    DashboardController: Object.assign(DashboardController, DashboardController),
TransaksiPOSController: Object.assign(TransaksiPOSController, TransaksiPOSController),
TransaksiController: Object.assign(TransaksiController, TransaksiController),
ProdukController: Object.assign(ProdukController, ProdukController),
AngsuranController: Object.assign(AngsuranController, AngsuranController),
KonversiStokController: Object.assign(KonversiStokController, KonversiStokController),
GoodsInController: Object.assign(GoodsInController, GoodsInController),
StockAdjustmentController: Object.assign(StockAdjustmentController, StockAdjustmentController),
}

export default Kasir