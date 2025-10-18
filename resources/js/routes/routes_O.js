import IndexComponent from '../../js/pages/IndexComponent.vue'
import Dashboard from '../pages/Dashboard.vue'
import ProductMaterial from '../pages/ProductMaterial.vue'
import ClassificationQty from '../pages/ClassificationQty.vue'
import UserMaster from '../../js/pages/UserMaster.vue'
import DropdownMaster from '../../js/pages/DropdownMaster.vue'
import useFetch from '../composables/utils/useFetch';
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
        path: '',
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
                path: 'product_material',
                name: 'product_material',
                beforeEnter: checkIfSessionExist,
                component: ProductMaterial,

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
                path: 'classification_qty/:itemsId',
                name: 'ClassificationQty',
                beforeEnter: checkIfSessionExist,
                component: ClassificationQty,
                props: true,
            }
        ]
    }
];
