import Auth from './Auth'
import Commentable from './Commentable'
import FinderController from './FinderController'
import Followable from './Followable'
import HuntController from './HuntController'
import Likeable from './Likeable'
import Profile from './Profile'
import PublicProfileController from './PublicProfileController'
import Welcome from './Welcome'
import Settings from './Settings'

const Controllers = {
    Auth: Object.assign(Auth, Auth),
    Commentable: Object.assign(Commentable, Commentable),
    FinderController: Object.assign(FinderController, FinderController),
    Followable: Object.assign(Followable, Followable),
    HuntController: Object.assign(HuntController, HuntController),
    Likeable: Object.assign(Likeable, Likeable),
    Profile: Object.assign(Profile, Profile),
    PublicProfileController: Object.assign(PublicProfileController, PublicProfileController),
    Welcome: Object.assign(Welcome, Welcome),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers