<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">User Master</h4>
        <div class="card mt-3"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="table-responsive">
                    <!-- id="dataTable" -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button @click="btnAddUser" type="button" ref= "btnAddUser" class="btn btn-primary btn-sm">
                                <font-awesome-icon class="nav-icon" icon="fas fa-user" />&nbsp; Add User
                            </button>
                        </div>
                    </div>
                    <DataTable
                        width="100%" cellspacing="0"
                        class="table mt-2"
                        ref="tblUserMaster"
                        :columns="userMasterColumns"
                        ajax="api/get_user_master"
                        :options="{
                            serverSide: true, //Serverside true will load the network
                            columnDefs:[
                                // {orderable:false,target:[0]}
                            ]
                        }"
                    >
                        <thead>
                            <tr>
                                <th>
                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                </th>
                                <th>Roles</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Section / Department</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent @add-event="formAddUser" icon="fa-download" modalDialog="modal-dialog modal-md" title="Add User" ref="modalAddUser">
        <template #body>
            <div class="row mt-3">
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Full Name:</span>
                        <Multiselect
                            v-model="frmUser.rapidxUser"
                            :options="settingVar.optRapidxUser"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button>
        </template>
    </ModalComponent>
</template>

<script setup>
     import {
        onMounted,
        ref,
        reactive,
        toRef,
    } from 'vue'
    import Swal from 'sweetalert2';
    import ModalComponent from '../components/ModalComponent.vue';
    import useSettings from '../composables/settings.js';
    import useForm from '../../js/composables/utils/useForm.js'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import useCommon from '../../js/composables/common.js';
    const {
        resetEcrForm,
    } = useCommon();

    DataTable.use(DataTablesCore);
    const { axiosSaveData } = useForm(); // Call the useFetch function

    const {
        getRapidxUserByIdOpt,
        getNoModuleRapidxUserByIdOpt,
    } = useSettings();

    const tblUserMaster = ref(null);
    const modalAddUser = ref(null);
    const modal = ref(null);
    const frmUser = ref({
        rapidxUser: null
    });
    const settingVar = reactive({
        optRapidxUser: []
    });
    const userMasterColumns = [
        { data: 'get_action',
        orderable: false,
            searchable: false,
            createdCell(cell){
                let btnUserMasterDetails = cell.querySelector('#btnUserMasterDetails');
                if(btnUserMasterDetails !=null){
                    btnUserMasterDetails.addEventListener('click',function(){
                        let dataId = this.getAttribute('data-id');
                        Swal.fire({
                            title: 'Confirmation',
                            text: 'Are you sure you want this user to change role?',
                            icon: 'warning',
                            allowOutsideClick: false,
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                saveUserApprover(dataId);
                            }
                        })
                    });
                }
            }
         },
        { data: 'get_roles'},
        { data: 'name'},
        { data: 'email'},
        { data: 'get_departments'}
    ];

    const rapidxUserParams = {
        globalVar: settingVar.optRapidxUser,
        formModel: toRef(frmUser.value,'rapidxUser'),
        selectedVal: "",
    };
    onMounted ( async () =>{
        modal.AddUser = new Modal(modalAddUser.value.modalRef,{keyboard:false});
        // modal.AddUser.show();
        getNoModuleRapidxUserByIdOpt(rapidxUserParams);
        modalAddUser.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmUser.value);
        })
    })

    const saveUserApprover = async (userId) => {
        let formData = new FormData();
        formData.append('userId',userId)
        axiosSaveData(formData,'api/save_user_approver', (response) =>{
            tblUserMaster.value.dt.draw();
        });
    }
    const btnAddUser = async () => {
        modal.AddUser.show();
    }

    const formAddUser = async () => {
        let formData = new FormData();

        formData.append('rapidxUser',frmUser.value.rapidxUser);

        axiosSaveData(formData,'api/save_rapidx_user', (response) =>{
            tblUserMaster.value.dt.draw();
            modal.AddUser.hide();
        });
    }
</script>
<style lang="scss" scoped>

</style>

