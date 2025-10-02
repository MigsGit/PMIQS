<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">PMI Quation System</h4>
        <div class="card mt-5"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="container-fluid px-4">
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6" v-show="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                <div class="card bg-dark text-white mb-4">
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="users" />&nbsp;User Master</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'UserMaster' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-dark text-white mb-4">
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="square-caret-down" />&nbsp;Dropdown Master</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'DropdownMaster' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref , onMounted,reactive, toRef} from 'vue';
    import useEcr from '../composables/ecr.js';

    const {
        axiosFetchData
    } = useEcr();

    //ref state
    const departmentGroup = ref(0);

    const getAdminAccessOpt = async () => {
        axiosFetchData({},'api/get_admin_access_opt',function(response){
            departmentGroup.value = response.data.departmentGroup;
        });
    }
    onMounted(async () => {
        await getAdminAccessOpt();
    })
</script>

<style lang="scss" scoped>

</style>
