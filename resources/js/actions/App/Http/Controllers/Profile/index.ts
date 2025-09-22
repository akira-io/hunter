import AboutController from './AboutController';
import AcademicBackgroundController from './AcademicBackgroundController';
import HighlightSkillController from './HighlightSkillController';
import LinksController from './LinksController';

const Profile = {
    AboutController: Object.assign(AboutController, AboutController),
    AcademicBackgroundController: Object.assign(AcademicBackgroundController, AcademicBackgroundController),
    HighlightSkillController: Object.assign(HighlightSkillController, HighlightSkillController),
    LinksController: Object.assign(LinksController, LinksController),
};

export default Profile;
