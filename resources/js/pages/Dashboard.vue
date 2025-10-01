<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">4M Change Control Management System</h4>
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
                                        <h4><font-awesome-icon class="nav-icon" icon="square-caret-down" />&nbsp;Ecr Requirement Master</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'EcrRequirementMaster' }">
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

                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="ecrApproval != 0 ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="ticket-alt" />&nbsp;ECR ({{ ecrApproval }})</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingEcr}} )
                                        Approved ( {{approvedEcr}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-end">
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Ecr' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="manApproval != 0  || pmiApprovalManDetail != 0  ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="user" />&nbsp;Man ({{ manApproval }})</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingMan}} )
                                        Approved ( {{approvedMan}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        PMI Approval ( {{pmiApprovalManDetail}} )
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Man' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="materialApproval != 0 || pmiApprovalMaterial != 0 ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="truck-fast" />&nbsp;Material ({{ materialApproval }})</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingMaterial}} )
                                        Approved ( {{approvedMaterial}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        PMI Approval ( {{pmiApprovalMaterial}} )
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Material' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="machineApproval != 0 || pmiApprovalMachine != 0 ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="microscope" />&nbsp;Machine ({{ machineApproval }})</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingMachine}} )
                                        Approved ( {{approvedMachine}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        PMI Approval ( {{pmiApprovalMachine}} )
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Machine' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="methodApproval != 0  || pmiApprovalMethod != 0  ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="clipboard-list" />&nbsp;Method ({{ methodApproval }})</h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingMethod}} )
                                        Approved ( {{approvedMethod}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        PMI Approval ( {{pmiApprovalMethod}} )
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Method' }">
                                            more info
                                            <font-awesome-icon class="nav-icon" icon="angle-right" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-white mb-4" :class="pmiApprovalEnvironment != 0  ? 'bg-danger' : 'bg-dark'" >
                                    <div class="card-body">
                                        <h4><font-awesome-icon class="nav-icon" icon="tree" />&nbsp;Environment </h4>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-start" v-if="departmentGroup === 'ISS' || departmentGroup === 'QAD'">
                                        Pending ( {{pendingEnvironment}} )
                                        Approved ( {{approvedEnvironment}} )
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        PMI Approval ( {{pmiApprovalEnvironment}} )
                                        <router-link class="small text-white stretched-link" :to="{ name: 'Environment' }">
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
    const ecrApproval = ref(0);
    const manApproval = ref(0);
    const materialApproval = ref(0);
    const machineApproval = ref(0);
    const methodApproval = ref(0);

    const pmiApprovalManDetail = ref(0);
    const pmiApprovalMaterial = ref(0);
    const pmiApprovalMachine = ref(0);
    const pmiApprovalMethod = ref(0);
    const pmiApprovalEnvironment = ref(0);
    const approvedEcr = ref(0);
    const pendingEcr = ref(0);
    const departmentGroup = ref(0);
    const pendingMan = ref(0);
    const approvedMan = ref(0);
    const pendingMaterial = ref(0);
    const approvedMaterial = ref(0);
    const pendingMethod = ref(0);
    const approvedMethod = ref(0);
    const pendingMachine = ref(0);
    const approvedMachine = ref(0);
    const pendingEnvironment = ref(0);
    const approvedEnvironment = ref(0);
    const getAdminAccessOpt = async () => {
        axiosFetchData({},'api/get_admin_access_opt',function(response){
            departmentGroup.value = response.data.departmentGroup;
        });
    }
    onMounted(async () => {
        await getAdminAccessOpt();

        let apiParams = {}

        axiosFetchData(apiParams,'api/get_approval_count_by_rapidx_user_id',function(response){
            let data = response.data;
            let pmiApproval = data.pmiApproval;

            ecrApproval.value = data.ecrApproval;
            manApproval.value = data.manApproval;
            materialApproval.value = data.materialApproval;
            machineApproval.value = data.machineApproval;
            methodApproval.value = data.methodApproval;
            pmiApprovalManDetail.value = pmiApproval.man_detail;
            pmiApprovalMaterial.value = pmiApproval.material;
            pmiApprovalMachine.value = pmiApproval.machine;
            pmiApprovalMethod.value = pmiApproval.method;
            pmiApprovalEnvironment.value = pmiApproval.environment;

            approvedEcr.value = data.approvedEcr;
            pendingEcr.value = data.pendingEcr;
            pendingMan.value =  data.pendingMan
            approvedMan.value =  data.approvedMan

            pendingMaterial.value =  data.pendingMaterial
            approvedMaterial.value =  data.approvedMaterial

            // pendingMethod.value =  data.pendingMethod
            // approvedMethod.value =  data.approvedMethod

            pendingMachine.value =  data.pendingMachine
            approvedMachine.value =  data.approvedMachine

            // pendingEnvironment =  data.pendingEnvironment
            // approvedEnvironment.value =  data.approvedEnvironment
        });
    })
</script>

<style lang="scss" scoped>

</style>
