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
                            <div class="col-xl-3 col-md-6" v-show="departmentGroup === 'ISS' || departmentGroup === 'PPC'">
                                <div class="card bg-dark text-white mb-4" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="users" />&nbsp;User Master</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        ( {{userCount}} ) Users
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
                                <div class="card text-white mb-4" :class="forYourApproval != 0 ? 'bg-danger' : 'bg-dark'">
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="fa-file" />&nbsp;Product / Material</h4>
                                    </div>
                                    <!-- <div class="card-footer d-flex align-items-center justify-content-end">

                                    </div> -->
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                      <p >  For your Approval ( {{forYourApproval}} )</p> &nbsp;
                                      <p  v-show="departmentGroup === 'ISS' || departmentGroup === 'PPC'"> Pending ( {{pending}} )  </p>  &nbsp;
                                      <p  v-show="departmentGroup === 'ISS' || departmentGroup === 'PPC'">  Approved ( {{approved}} ) </p>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'ProductMaterial' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-dark text-white mb-4">
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="cog" />&nbsp;Email Settings</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">

                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'ProductMaterial' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 d-none">
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
    import useFetch from '../composables/utils/useFetch';

    const {
        axiosFetchData
    } = useFetch();

    //ref state
    const departmentGroup = ref(0);
    const pending = ref(0);
    const approved = ref(0);
    const userCount = ref(0);
    const forYourApproval = ref(0);

    const getAdminAccessOpt = async () => {
        axiosFetchData({},'api/get_admin_access_opt',function(response){
            departmentGroup.value = response.data.departmentGroup;
        });
    }

    const getPendingApproved = async () => {
        axiosFetchData({},'api/get_pending_approved',function(response){
            let data = response.data;
            userCount.value = data.userCollection;
            pending.value = data.pmItemCollectionPending;
            approved.value = data.pmItemCollectionApproved;
            forYourApproval.value = data.pmItemApprovalPending;
        });
    }
    onMounted(async () => {
        await getAdminAccessOpt();
        await getPendingApproved();
    })
</script>

<style lang="scss" scoped>

</style>
