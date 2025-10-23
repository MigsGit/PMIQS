<template>
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <!-- <li class="nav-link"> -->
                    <a href="/RapidX" class="nav-link">
                        <font-awesome-icon class="nav-icon" icon="arrow-left" />&nbsp;Return to RapidX
                    </a>
                <!-- </li> -->
                <router-link class="nav-link" :to="{ name: 'dashboard' }">
                    <font-awesome-icon class="nav-icon" icon="gauge-high" />&nbsp;Dashboard
                </router-link>
                <router-link class="nav-link" :to="{ name: 'ProductMaterial' }">
                    <font-awesome-icon class="nav-icon" icon="fa fa-file" />&nbsp;ProductMaterial
                </router-link>
                <router-link v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'" class="sb-nav-link-icon nav-link" :to="{ name: 'UserMaster' }">
                    <font-awesome-icon class="nav-icon" icon="users" />&nbsp; User Master
                </router-link>

                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'DropdownMaster' }">
                    <font-awesome-icon class="nav-icon" icon="square-caret-down" />&nbsp; Dropdown Master
                </router-link>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <!-- {{ userFullName }} -->
            <div class="small">Logged in: {{ userFullName }}</div>
        </div>
    </nav>
</template>
<script setup>
    import {ref , onMounted,reactive, toRef} from 'vue';
    import { useAuthStore } from '../stores';
    const useAuth = useAuthStore();
    import useFetchAxios from "../composables/utils/useFetch";

    const {
        axiosFetchData,
    } = useFetchAxios();


    const userFullName = ref(null);
    const departmentGroup = ref(null);

    const getUserFullName = async () => {
        axiosFetchData({},'api/get_admin_access_opt',function(response){
            userFullName.value = response.data.activeUserFullName;
            departmentGroup.value = response.data.departmentGroup;
        });
    }
    onMounted( async () => {
        await getUserFullName();
    });

</script>