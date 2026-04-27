import DashboardController from './DashboardController'
import ProdukController from './ProdukController'
import ReportController from './ReportController'
import SettingsController from './SettingsController'
import PenggunaController from './PenggunaController'
import KategoriController from './KategoriController'
import TransaksiController from './TransaksiController'
import TrustScoreController from './TrustScoreController'
import GoodsInApprovalController from './GoodsInApprovalController'
import GoodsInHistoryController from './GoodsInHistoryController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
ProdukController: Object.assign(ProdukController, ProdukController),
ReportController: Object.assign(ReportController, ReportController),
SettingsController: Object.assign(SettingsController, SettingsController),
PenggunaController: Object.assign(PenggunaController, PenggunaController),
KategoriController: Object.assign(KategoriController, KategoriController),
TransaksiController: Object.assign(TransaksiController, TransaksiController),
TrustScoreController: Object.assign(TrustScoreController, TrustScoreController),
GoodsInApprovalController: Object.assign(GoodsInApprovalController, GoodsInApprovalController),
GoodsInHistoryController: Object.assign(GoodsInHistoryController, GoodsInHistoryController),
}

export default Admin