import Broadcasting from './Broadcasting';
import Routing from './Routing';

const Illuminate = {
    Routing: Object.assign(Routing, Routing),
    Broadcasting: Object.assign(Broadcasting, Broadcasting),
};

export default Illuminate;
