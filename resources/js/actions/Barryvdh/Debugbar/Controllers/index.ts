import OpenHandlerController from './OpenHandlerController'
import AssetController from './AssetController'
import CacheController from './CacheController'
import QueriesController from './QueriesController'

const Controllers = {
    OpenHandlerController: Object.assign(OpenHandlerController, OpenHandlerController),
    AssetController: Object.assign(AssetController, AssetController),
    CacheController: Object.assign(CacheController, CacheController),
    QueriesController: Object.assign(QueriesController, QueriesController),
}

export default Controllers