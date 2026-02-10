<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Email Settingssss</h4>
        <div class="card mt-3"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="table-responsive">
                    <!-- id="dataTable" -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button @click="btnAddEmail" type="button" ref= "btnAddUser" class="btn btn-primary btn-sm">
                                <font-awesome-icon class="nav-icon" icon="fas fa-user" />&nbsp; Add Email
                            </button>
                        </div>
                    </div>
                    <DataTable
                        width="100%" cellspacing="0"
                        class="table mt-2"
                        ref="tblEmailMaster"
                        :columns="emailMasterColumns"
                        ajax="api/load_dropdown_customer_groups"
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
                                <th>Customer</th>
                                <th>Recipients Cc</th>
                                <th>Recipients To</th>
                                <th>Updated By</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent @add-event="saveCustomerGroupDetails" icon="fa-download" modalDialog="modal-dialog modal-md" title="Add User" ref="modalSaveEmail">
        <template #body>
            <div class="row mt-3">
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Customer Name:</span>
                        <input v-model="frmSaveEmail.dropdownCustomerGroupsId" type="text" class="form-control" id="inlineFormInputGroup">
                    </div>
                </div>
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Customer Name:</span>
                        <input v-model="frmSaveEmail.customerName" type="text" class="form-control" id="inlineFormInputGroup">
                    </div>
                </div>
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Email To:
                        </span>
                        <textarea v-model="frmSaveEmail.emailTo" type="text" class="form-control" id="inlineFormInputGroup"> </textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Email CC:</span>
                        <textarea v-model="frmSaveEmail.emailCc" type="text" class="form-control" id="inlineFormInputGroup"> </textarea>
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
    import useForm from '../composables/utils/useForm.js'
    import useFetch from '../composables/utils/useFetch.js'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import useCommon from '../composables/common.js';
    const {
        resetEcrForm,
    } = useCommon();

    DataTable.use(DataTablesCore);
    const { axiosSaveData } = useForm(); // Call the useFetch function
    const { axiosFetchData } = useFetch(); // Call the useFetch function

    const {
        settingsVar,
        frmSaveEmail,
        getRapidxUserByIdOpt,
        getNoModuleRapidxUserByIdOpt,
    } = useSettings();

    const tblEmailMaster = ref(null);
    const modal = ref(null);
    const modalSaveEmail = ref(null);
    const frmUser = ref({
        rapidxUser: null
    });

    const emailMasterColumns = [
        { data: 'get_action',
        orderable: false,
            searchable: false,
            createdCell(cell){
                let btnCustomerMasterDetails = cell.querySelector('#btnCustomerMasterDetails');
                if(btnCustomerMasterDetails !=null){
                    btnCustomerMasterDetails.addEventListener('click',function(){
                        let dataId = this.getAttribute('data-id');
                        let paramsCustomerGroupDetails = {
                            customerMasterDetailsId : dataId
                        }   
                        getCustomerGroupDetailsById(paramsCustomerGroupDetails)
                    });
                }
            }
         },
        { data: 'dd_customer_groups_id'},
        { data: 'recipients_cc'},
        { data: 'recipients_to'},
        { data: 'get_updated_by'}
    ];

    // const rapidxUserParams = {
    //     globalVar: settingsVar.optRapidxUser,
    //     formModel: toRef(frmUser.value,'rapidxUser'),
    //     selectedVal: "",
    // };
    onMounted ( async () =>{
        modal.SaveEmail = new Modal(modalSaveEmail.value.modalRef,{keyboard:false});
        modalSaveEmail.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmSaveEmail.value);
        })
    })

    const getCustomerGroupDetailsById = async (params) => {
        let apiParams = {
            customerMasterDetailsId : params.customerMasterDetailsId
        }
        axiosFetchData(apiParams,'api/get_customer_group_details_by_id',function(response){
            let data = response.data.dropdownCustomerGroupResource[0];

            frmSaveEmail.value.dropdownCustomerGroupsId = data.id;
            frmSaveEmail.value.customerName = data.customer;
            frmSaveEmail.value.emailTo = data.recipientsTo;
            frmSaveEmail.value.emailCc = data.recipientsCc;
            modal.SaveEmail.show();
        });
    }
    const saveCustomerGroupDetails = async (userId) => {
        let formData = new FormData();
        formData.append('dropdownCustomerGroupsId',frmSaveEmail.value.dropdownCustomerGroupsId)
        formData.append('customerName',frmSaveEmail.value.customerName)
        formData.append('emailTo',frmSaveEmail.value.emailTo)
        formData.append('emailCc',frmSaveEmail.value.emailCc)
        axiosSaveData(formData,'api/save_customer_group_details', (response) =>{
            tblEmailMaster.value.dt.draw();
            modal.SaveEmail.hide();
        });
    }
    // const btnAddEmail = async () => {
    //     modal.SaveEmail.show();
    // }

    // const formAddUser = async () => {
    //     let formData = new FormData();

    //     formData.append('rapidxUser',frmUser.value.rapidxUser);

    //     axiosSaveData(formData,'api/save_rapidx_user', (response) =>{
    //         tblUserMaster.value.dt.draw();
    //         modal.SaveEmail.hide();
    //     });
    // }
</script>
<style lang="scss" scoped>

</style>

