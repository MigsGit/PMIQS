<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Material / Product Quotation</h4>
        <div class="card mt-3"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="table-responsive">
                    <!-- id="dataTable" -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button @click="btnAddUser" type="button" ref= "btnAddUser" class="btn btn-primary btn-sm">
                                <font-awesome-icon class="nav-icon" icon="fas fa-file" />&nbsp; Add New
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent @add-event="formProductMaterial" icon="fa-download" modalDialog="modal-dialog modal-md" title="Add User" ref="modalAddUser">
        <template #body>
            <div class="row mt-3">
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Full Name:</span>
                        <Multiselect
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
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import ModalComponent from '../components/ModalComponent.vue';

    DataTable.use(DataTablesCore);


    onMounted ( async () =>{

    })

</script>
<style lang="scss" scoped>

</style>

