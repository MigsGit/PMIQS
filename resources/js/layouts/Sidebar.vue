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
                    <!-- <i class="fas fa-tachometer-alt" icon="fa-tachometer-alt"></i>&nbsp;Dashboard get_bulletin_pages_by_id-->
                </router-link>
                <div class="sb-sidenav-menu-heading">User Access</div>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Ecr' }">
                    <font-awesome-icon class="nav-icon" icon="ticket-alt" />&nbsp;&nbsp; ECR
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Man' }">
                    <font-awesome-icon class="nav-icon" icon="user" />&nbsp; Man
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Material' }">
                    <font-awesome-icon class="nav-icon" icon="truck-fast" />&nbsp; Material
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Machine' }">
                    <font-awesome-icon class="nav-icon" icon="microscope" />&nbsp; Machine
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Method' }">
                    <font-awesome-icon class="nav-icon" icon="clipboard-list" />&nbsp; Method
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Environment' }">
                    <font-awesome-icon class="nav-icon" icon="tree" />&nbsp; Environment
                </router-link>
                <div class="sb-sidenav-menu-heading">Settings</div>

                <router-link v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'" class="sb-nav-link-icon nav-link" :to="{ name: 'UserMaster' }">
                <!-- <router-link v-show="true" class="sb-nav-link-icon nav-link" :to="{ name: 'UserMaster' }"> -->
                    <font-awesome-icon class="nav-icon" icon="users" />&nbsp; User Master
                </router-link>

                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'DropdownMaster' }">
                    <font-awesome-icon class="nav-icon" icon="square-caret-down" />&nbsp; Dropdown Master
                </router-link>
                <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'EcrRequirementMaster' }">
                    <font-awesome-icon class="nav-icon" icon="clipboard-list" />&nbsp; Ecr Requirement List
                </router-link>
                <!--  <router-link class="sb-nav-link-icon nav-link" :to="{ name: 'Settings' }">
                    <i class="fas fa-tasks"></i>&nbsp; Settings
                </router-link> -->
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
    import useEcr from '../../js/composables/ecr.js';

    import { useAuthStore } from '../stores';
    const useAuth = useAuthStore();
    const {
        axiosFetchData
    } = useEcr();

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