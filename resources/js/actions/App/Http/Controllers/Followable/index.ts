import FollowController from './FollowController'
import GetHuntersController from './GetHuntersController'
import GetHuntingsController from './GetHuntingsController'
import UnFollowController from './UnFollowController'

const Followable = {
    FollowController: Object.assign(FollowController, FollowController),
    GetHuntersController: Object.assign(GetHuntersController, GetHuntersController),
    GetHuntingsController: Object.assign(GetHuntingsController, GetHuntingsController),
    UnFollowController: Object.assign(UnFollowController, UnFollowController),
}

export default Followable