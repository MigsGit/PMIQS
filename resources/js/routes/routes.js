import IndexComponent from '../../js/pages/IndexComponent.vue'
import Unauthorized from '../../js/pages/Unauthorized.vue'
import Dashboard from '../pages/Dashboard.vue'
import Ecr from '../pages/Ecr.vue'
import Man from '../../js/pages/Man.vue'
import Material from '../pages/Material.vue'
import Machine from '../../js/pages/Machine.vue'
import Method from '../../js/pages/Method.vue'
import Environment from '../../js/pages/Environment.vue'
import UserMaster from '../../js/pages/UserMaster.vue'
import DropdownMaster from '../../js/pages/DropdownMaster.vue'
import EcrRequirementMaster from '../../js/pages/EcrRequirementMaster.vue'
import useFetch from '../../js/composables/utils/useFetch';
const { axiosFetchData } = useFetch(); // Call  the useFetch function

function checkIfSessionExist(to, from, next) {
    let apiParams;
    axiosFetchData(apiParams,'api/get_admin_access_opt',function(response){

        if(response.data.validUserAccessCount === 1){
            next();
        }else{
            // next({
            //     path: '/RapidX/',
            //     replace: true
            // });
            return window.location.href = '/RapidX';
        }
    });
}
export default [
    {
        path: '/PQS',
        beforeEnter: checkIfSessionExist,
        components: {
            default: IndexComponent,
            dashboard: Dashboard,
        },
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                beforeEnter: checkIfSessionExist,
                component: Dashboard,

            },
            {
                path: 'ecr',
                name: 'Ecr',
                beforeEnter: checkIfSessionExist,
                component: Ecr,
            },
            {
                path: 'man',
                name: 'Man',
                beforeEnter: checkIfSessionExist,
                component: Man,
            },
            {
                path: 'material',
                name: 'Material',
                beforeEnter: checkIfSessionExist,
                component: Material,
            },
            {
                path: 'machine',
                name: 'Machine',
                beforeEnter: checkIfSessionExist,
                component: Machine,
            },
            {
                path: 'machine',
                name: 'Machine',
                beforeEnter: checkIfSessionExist,
                component: Machine,
            },
            {
                path: 'method',
                name: 'Method',
                beforeEnter: checkIfSessionExist,
                component: Method,
            },
            {
                path: 'environment',
                name: 'Environment',
                beforeEnter: checkIfSessionExist,
                component: Environment,
            },
            {
                path: 'user_master',
                name: 'UserMaster',
                beforeEnter: checkIfSessionExist,
                component: UserMaster,
            },
            {
                path: 'dropdown_master',
                name: 'DropdownMaster',
                beforeEnter: checkIfSessionExist,
                component: DropdownMaster,
            },
            {
                path: 'ecr_requirement_master',
                name: 'EcrRequirementMaster',
                beforeEnter: checkIfSessionExist,
                component: EcrRequirementMaster,
            },
        ]
    }
];
