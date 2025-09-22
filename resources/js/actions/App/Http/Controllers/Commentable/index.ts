import DestroyCommentController from './DestroyCommentController'
import HuntCommentController from './HuntCommentController'

const Commentable = {
    DestroyCommentController: Object.assign(DestroyCommentController, DestroyCommentController),
    HuntCommentController: Object.assign(HuntCommentController, HuntCommentController),
}

export default Commentable