import Auth from './Auth'
import ProfileController from './ProfileController'
import Admin from './Admin'
import PelangganController from './PelangganController'
import Kasir from './Kasir'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
ProfileController: Object.assign(ProfileController, ProfileController),
Admin: Object.assign(Admin, Admin),
PelangganController: Object.assign(PelangganController, PelangganController),
Kasir: Object.assign(Kasir, Kasir),
}

export default Controllers