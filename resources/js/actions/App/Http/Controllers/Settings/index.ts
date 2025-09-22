import ProfileController from './ProfileController'
import PasswordController from './PasswordController'

const Settings = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController),
}

export default Settings