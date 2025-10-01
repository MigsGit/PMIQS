import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';
import useForm from "./utils/useForm";

export default function useEcr(){
    const { axiosFetchData } = useFetch(); // Call  the useFetch function
    const  { axiosSaveData } = useForm();

    //Constant Object
    const modal = {
        SaveEcrDetail : null,
    };
    const modalEcr = {};
    //Reactive State
    const ecrVar = reactive({
        optDescriptionOfChange: [],
        optReasonOfChange: [],

        optQadCheckedBy: [],
        optQadApprovedByInternal: [],

        requestedBy: [],
        technicalEvaluation: [],
        reviewedBy: [],

        preparedBy: [],
        checkedBy: [],
        approvedBy: [],

        optTypeOfPart: [],
        documentAffectedExternal: [
            {value: '1' , label:"QC Process Flow Chart"},
            {value: '2' , label:"Packaging Specification"},
        ],
    });
    //Ref State
    const frmEcr = ref({
        ecrsId: '',
        departmentGroup: '',
        ecrNo: '',
        approvalStatus: '',
        category: '',
        customerName: '',
        partName: '',
        productLine: '',
        section: '',
        internalExternal: '',
        partNumber: '',
        deviceName: '',
        customerEcNo: '',
        dateOfRequest: '',
        approvalRemarks: '',
    });
    const frmEcrDetails = ref({
        ecrDetailsId: '',
        ecrsId: '',
        typeOfPart: '',
        changeImpDate: '',
        docSubDate: '',
        docToBeSub: 'test',
        remarks: 'test',
    });
    const frmEcrReasonRows = ref([
        {
            descriptionOfChange: '',
            reasonOfChange: '',
        },
    ]);
    const frmEcrQadRows = ref([
        {
            qadCheckedBy: '',
            qadApprovedByInternal: '',
        },
    ]);
    const frmEcrOtherDispoRows = ref([
        {
            requestedBy: '',
            technicalEvaluation: '',
            reviewedBy: '',
        },
    ]);
    const frmEcrPmiApproverRows = ref([
        {
            preparedBy: '',
            checkedBy: '',
            approvedBy: '',
        },
    ]);
    const frmEcrPmiExternalApproverRows = ref([
        {
            preparedBy: '',
            checkedBy: '',
            approvedBy: '',
        },
    ]);
    const tblEcrDetails = ref(null);

     //Ecr Req
     const tblEcrManRequirements = ref(null);
     const tblEcrMatRequirements = ref(null);
     const tblEcrMachineRequirements = ref(null);
     const tblEcrMethodRequirements = ref(null);
     const tblEcrEnvironmentRequirements = ref(null);
     const tblEcrOthersRequirements = ref(null);

     const selectedEcrRequirementsIdEncrypted = ref(null);
     const arrEcrRequirementOriginalFilenames = ref(null);

     const isEmptyTblEcrManRequirements = ref(null);
     const isEmptyTblEcrMaterialRequirements = ref(null);
     const isEmptyTblEcrMachineRequirements = ref(null);
     const isEmptyTblEcrMethodRequirements = ref(null);
     const isEmptyTblEcrEnvironmentRequirements = ref(null);
     const isEmptyTblEcrOthersRequirements = ref(null);

    //Obj Params
    let descriptionOfChangeParams ={
        tblReference : 'ecr_doc',
        globalVar: ecrVar.optDescriptionOfChange,
        formModel: toRef(frmEcrReasonRows.value[0],'descriptionOfChange'), // Good Practice create a reactive reference to a property inside an object
        selectedVal: '',
    };
    let reasonOfChangeParams = {
        tblReference : 'ecr_roc',
        globalVar: ecrVar.optReasonOfChange,
        formModel: toRef(frmEcrReasonRows.value[0],'reasonOfChange'),
        selectedVal: '',
    };
    const typeOfPartParams = {
        tblReference : 'type_of_part',
        globalVar: ecrVar.optTypeOfPart,
        formModel: toRef(frmEcrDetails.typeOfPart,'reasonOfChange'),
        selectedVal: '',
    }
    //Functions
    const addEcrReasonRows = async () => {
        frmEcrReasonRows.value.push({
            descriptionOfChange: '',
            reasonOfChange: '',
        });
    }
    const removeEcrReasonRows = async (index) => {
        frmEcrReasonRows.value.splice(index,1);
    }
    const getDropdownMasterByOpt = async (params) => {
        let apiParams = {
            tblReference : params.tblReference
        }
        await axiosFetchData(apiParams, `api/get_dropdown_master_by_opt`, (response) => { //url
            let data = response.data;
            let dropdownMasterByOpt = data.dropdownMasterByOpt;
             /*
                Multiple option element base on globalVar
                This only reassigns the local globalVar.
                It does NOT modify the original ecrVar.optDropdownMasterDetails, because in Vue's reactive, reassigning won't trigger reactivity.
                You must mutate the array (not replace it) so Vue detects and updates it reactively.
                Use .splice() to update its contents.
            */
            params.globalVar.splice(0, params.globalVar.length,
                { value: '', label: '-Select an option-', disabled:true }, // Push "" option at the start
                // { value: 0, label: 'N/A' }, // Push "N/A" option at the start
                    ...dropdownMasterByOpt.map((value) => {
                    return {
                        value: value.id,
                        label: value.dropdown_masters_details
                    }
                }),
            );

            params.formModel.value = params.selectedVal; //Make sure the data type is correct | String or Array
        });
    }
    const getEcrById = async (ecrId) => {
        let params = {
            ecr_id : ecrId
        }
        axiosFetchData(params,'api/get_ecr_by_id',function(response){
            let data = response.data;
            let ecr = data.ecr;
            if(ecr.status != 'DIS'){
                modalEcr.EcrRequirements.show();
            }

            frmEcr.value.ecrsId = ecr.id;
            frmEcr.value.ecrNo = ecr.ecr_no;;
            frmEcr.value.category = ecr.category;
            frmEcr.value.customerName = ecr.customer_name;
            frmEcr.value.partNumber = ecr.part_no;
            frmEcr.value.partName = ecr.part_name;
            frmEcr.value.productLine = ecr.product_line;
            frmEcr.value.section = ecr.section;
            frmEcr.value.internalExternal = ecr.internal_external;
            frmEcr.value.deviceName = ecr.device_name;
            frmEcr.value.customerEcNo = ecr.customer_ec_no;
            frmEcr.value.dateOfRequest = ecr.date_of_request;
            frmEcr.value.approvalStatus = ecr.approval_status;
            //Multiselect
            frmEcrReasonRows.value = [];
            frmEcrQadRows.value = [];
            frmEcrPmiApproverRows.value = [];
            frmEcrOtherDispoRows.value = [];
            frmEcrPmiExternalApproverRows.value = [];
            let ecrApprovalCollection = data.ecrApprovalCollection;
            let pmiApprovalCollection = data.pmiApprovalCollection;
            let pmiExternalApprovalCollection = data.pmiExternalApprovalCollection;
            let ecrDetails = ecr.ecr_details;


            setTimeout(() => {  //Cannot display data immediately, need to wait for the DOM to be updated
                //Reasons
                if (ecrDetails.length != 0){
                    ecrDetails.forEach((ecrDetailsEl,index) =>{
                        frmEcrReasonRows.value.push({
                            descriptionOfChange : ecrDetailsEl.description_of_change,
                            reasonOfChange : ecrDetailsEl.reason_of_change
                        });
                    })
                }
            }, 100);
            setTimeout(() => { //Cannot display data immediately, need to wait for the DOM to be updated
                //ECR Approval
                if (ecrApprovalCollection.length != 0){
                    let requestedBy = ecrApprovalCollection.OTRB;
                    let technicalEvaluation = ecrApprovalCollection.OTTE;
                    let reviewedBy = ecrApprovalCollection.OTRVB;
                    let qaCheckedBy = ecrApprovalCollection.QACB;
                    let qaInternal = ecrApprovalCollection.QAIN;

                    // Find the key with the longest array, Loops through all keys using Object.keys(),Compares array lengths using .reduce(),Returns the key and array with the highest length
                    // Exclude 'QA' from keys
                    const ecrApprovalCollectionFiltered = Object.keys(ecrApprovalCollection).filter(key => key !== 'QA');
                    const maxKey = ecrApprovalCollectionFiltered.reduce((a, b) =>
                        ecrApprovalCollection[a].length > ecrApprovalCollection[b].length ? a : b
                    );
                    ecrApprovalCollection[maxKey].forEach((ecrApprovalsEl,index) => {
                        console.log('requestedBy',requestedBy[index]);

                        frmEcrOtherDispoRows.value.push({
                            requestedBy: requestedBy[index].rapidx_user_id ?? 0,
                            reviewedBy: reviewedBy[index].rapidx_user_id ?? 0,
                            technicalEvaluation: technicalEvaluation[index].rapidx_user_id ?? 0,
                        });
                    });
                    //QA Approval
                    if (qaCheckedBy.length != 0){
                        frmEcrQadRows.value.qadCheckedBy =  qaCheckedBy[0].rapidx_user_id ?? 0;
                        frmEcrQadRows.value.qadApprovedByInternal = qaInternal[0].rapidx_user_id ?? 0;
                    }
                }
                //PMI Approval
                if (pmiApprovalCollection.length != 0){
                    let preparedBy = pmiApprovalCollection.PB;
                    let checkedBy = pmiApprovalCollection.CB;
                    let approvedBy = pmiApprovalCollection.AB;

                    // Find the key with the longest array, Loops through all keys using Object.keys(),Compares array lengths using .reduce(),Returns the key and array with the highest length
                    const maxKey = Object.keys(pmiApprovalCollection).reduce((a, b) =>
                        pmiApprovalCollection[a].length > pmiApprovalCollection[b].length ? a : b
                    );

                    pmiApprovalCollection[maxKey].forEach((ecrApprovalsEl,index) => {
                        frmEcrPmiApproverRows.value.push({
                            preparedBy: preparedBy[index].rapidx_user_id ?? 0,
                            checkedBy: checkedBy[index].rapidx_user_id ?? 0,
                            approvedBy:approvedBy[index].rapidx_user_id ?? 0,
                        });
                        if(ecr.internal_external === "External"){
                            let externalPreparedBy = pmiApprovalCollection.EXQC;
                            let externalPheckedBy = pmiApprovalCollection.EXOH;
                            let externalPpprovedBy = pmiApprovalCollection.EXQA;
                            frmEcrPmiExternalApproverRows.value.push({
                                preparedBy: externalPreparedBy[index].rapidx_user_id ?? 0,
                                checkedBy: externalPheckedBy[index].rapidx_user_id ?? 0,
                                approvedBy:externalPpprovedBy[index].rapidx_user_id ?? 0,
                            });
                        }
                    });
                }

            }, 1000);
            modalEcr.SaveEcr.show();
        });
    }
    const resetArrEcrRows = async () => {
        frmEcrQadRows.value.qadCheckedBy =  '';
        frmEcrQadRows.value.qadApprovedByInternal = '';
        frmEcrPmiApproverRows.value = [];
        frmEcrPmiApproverRows.value.push({
            preparedBy: '' ,
            checkedBy: '' ,
            approvedBy: '' ,
        });
    }
    const getEcrDetailsId = async (ecrDetailsId) =>
    {
        let params = {
            ecrDetailsId : ecrDetailsId
        }
        axiosFetchData(params,'api/get_ecr_details_id',function(response){
            let ecrDetails = response.data.ecrDetail;
            frmEcrDetails.value.ecrDetailsId = ecrDetailsId;
            frmEcrDetails.value.changeImpDate =ecrDetails.change_imp_date
            frmEcrDetails.value.docSubDate =ecrDetails.doc_sub_date
            frmEcrDetails.value.docToBeSub =ecrDetails.doc_to_be_sub
            frmEcrDetails.value.customerApproval = ecrDetails.customer_approval
            frmEcrDetails.value.remarks =ecrDetails.remarks
            frmEcrDetails.value.typeOfPart = ecrDetails.dropdown_master_detail_type_of_part  === null ? 0: ecrDetails.dropdown_master_detail_type_of_part.id;
            frmEcrReasonRows.value[0].descriptionOfChange = ecrDetails.dropdown_master_detail_description_of_change.id;
            frmEcrReasonRows.value[0].reasonOfChange = ecrDetails.dropdown_master_detail_reason_of_change.id;
        });
    }
    const saveEcrDetails = async () => {
        let formData = new FormData();
        //Append form data
        [
            ["ecr_details_id", frmEcrDetails.value.ecrDetailsId],
            ["change_imp_date", frmEcrDetails.value.changeImpDate],
            ["type_of_part", frmEcrDetails.value.typeOfPart],
            ["doc_sub_date", frmEcrDetails.value.docSubDate],
            ["doc_to_be_sub", frmEcrDetails.value.docToBeSub],
            ["customer_approval", frmEcrDetails.value.customerApproval],
            ["remarks", frmEcrDetails.value.remarks],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_ecr_details', (response) =>{
            tblEcrDetails.value.dt.draw();
            modalEcr.SaveEcrDetail.hide();
        });
    }

    //Ecr Requirements
    const tblEcrRequirementsColumns = [
        {   data: 'requirement', title: 'Requirement'} ,
        {   data: 'details', title: 'Details'} ,
        {   data: 'evidence', title: 'Evidence'} ,
        {   data: 'get_actions',
            title: 'Action',
            createdCell(cell){
                let btnChangeEcrReqDecision = cell.querySelector('#btnChangeEcrReqDecision');
                if(btnChangeEcrReqDecision != null){
                    btnChangeEcrReqDecision.addEventListener('change',function(){
                        let ecrReqId = this.getAttribute('ecr-requirements-id');
                        let ecrReqValue = this.value;
                        let classificationRequirementId = this.getAttribute('classification-requirement-id');
                        let ecrReqDecisionParams = {
                            ecrReqId : ecrReqId,
                            ecrReqValue : ecrReqValue,
                            classificationRequirementId : classificationRequirementId,
                            btnChangeEcrReqDecisionClass: this.classList,
                        }

                        ecrReqDecisionChange(ecrReqDecisionParams);
                    });
                }
            }
        },
          // File Upload & View columns...
        {
            data: null, // No specific data field, as this is for custom rendering
            title: 'Upload',
            orderable: false,
            searchable: false,
            render: () => '', // Leave empty initially
            createdCell(cell, cellData, rowData) {
                // Create the file input element dynamically
                const inputGroup = document.createElement('div');
                inputGroup.className = 'input-group flex-nowrap mb-2 input-group-sm';

                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.multiple = true;
                fileInput.accept = '.pdf';
                fileInput.className = 'form-control form-control-lg';
                fileInput.setAttribute('classifications-id', rowData.classifications_id);
                fileInput.setAttribute('classification-requirements-id', rowData.id);

                if(rowData.ecr_requirement !=null){
                    fileInput.setAttribute('ecr-requirements-id', rowData.ecr_requirement.id);
                    fileInput.setAttribute('ecrs-id', rowData.ecr_requirement.ecrs_id);
                }

                // console.log('rowData', rowData); // Assuming `id` exists in rowData

                // Add an event listener for file change
                fileInput.addEventListener('change', (event) => {
                    let files = Array.from(event.target.files);
                    let uploadFilesParams = {
                        classificationsId: rowData.classifications_id,
                        classificationRequirementsId: rowData.id,
                        ecrRequirementId: rowData.ecr_requirement.id,
                        ecrsId: rowData.ecr_requirement.ecrs_id,
                        requirement: rowData.requirement,
                    };

                    uploadFiles(uploadFilesParams, files);
                    // Reset input to allow same file re-selection
                    event.target.value = '';
                });

                inputGroup.appendChild(fileInput);
                cell.appendChild(inputGroup);
            },
        },
        {   data: 'get_view_ecr_req_ref',
            title: 'View',
            createdCell(cell){
                let btnViewEcrRequirementRef = cell.querySelector('#btnViewEcrRequirementRef');
                if(btnViewEcrRequirementRef != null){
                    btnViewEcrRequirementRef.addEventListener('click',function(){
                        let ecrRequirementsId = this.getAttribute('ecr-requirements-id');
                        let ecrsId = this.getAttribute('ecrs-id');
                        let ecrRequirementParams = {
                            ecrRequirementsId: ecrRequirementsId,
                            ecrsId: ecrsId,
                        }
                        getEcrRequirementRefById(ecrRequirementParams);
                    });
                }
            }
        },
    ];
    // Function to handle file upload
    const uploadFiles = async (uploadFilesParams, files) => {
        uploadFilesParams;
        let result = '';
        Swal.fire({
            title: 'Confirmation',
            text: 'Are you sure you want to upload this Evidence / Reference ?',
            icon: 'warning',
            allowOutsideClick: false,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                files.forEach((file) => {
                    formData.append('ecrRequirementFile[]', file);
                });
                formData.append('category',uploadFilesParams.classificationsId);
                formData.append('classificationRequirementsId',uploadFilesParams.classificationRequirementsId);
                formData.append('ecrRequirementId',uploadFilesParams.ecrRequirementId);
                formData.append('ecrsId',uploadFilesParams.ecrsId);

                axiosSaveData(formData,'api/upload_ecr_requirement_ref', (response) =>{
                    //Load ECR Requirement by Category and Ecrs Id
                    tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+uploadFilesParams.ecrsId).draw();
                    tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+uploadFilesParams.ecrsId).draw();
                    tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+uploadFilesParams.ecrsId).draw();
                    tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+uploadFilesParams.ecrsId).draw();
                    tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+uploadFilesParams.ecrsId).draw();
                    // tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+uploadFilesParams.ecrsId).draw();
                });
            }
         })
    };
    const getEcrRequirementRefById = async (ecrRequirementParams) => {
        let apiParams = {
            ecrRequirementsId: ecrRequirementParams.ecrRequirementsId,
            ecrsId: ecrRequirementParams.ecrsId,
        }
        axiosFetchData(apiParams,'api/get_ecr_requirement_ref_by_id',function(response){
            let data = response.data;
            let ecrRequirementsId = data.ecrRequirementsId;
            let originalFilename = data.originalFilename;
            arrEcrRequirementOriginalFilenames.value = originalFilename;
            selectedEcrRequirementsIdEncrypted.value = ecrRequirementsId;
            modalEcr.ViewEcrRequirementRef.show();
        });
    }
    const btnLinkViewEcrRequirementRef = async (selectedEcrRequirementsIdEncrypted,index) => { //view_material_ref
        window.open(`api/view_ecr_requirement_ref?ecrRequirementsId=${selectedEcrRequirementsIdEncrypted} &&  && index=${index}`, '_blank');
    }
    const btnEcrRequirement = async (ecrsId) => {
        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
        // tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();
        modalEcr.EcrRequirements.show();

    }

    return {
        modalEcr,
        ecrVar,
        frmEcr,
        frmEcrDetails,
        frmEcrReasonRows,
        frmEcrQadRows,
        frmEcrOtherDispoRows,
        frmEcrPmiApproverRows,
        frmEcrPmiExternalApproverRows,
        descriptionOfChangeParams,
        reasonOfChangeParams,
        typeOfPartParams,
        tblEcrDetails,
        resetArrEcrRows,
        getDropdownMasterByOpt,
        axiosFetchData,
        getEcrById,
        addEcrReasonRows,
        removeEcrReasonRows,
        getEcrDetailsId,
        saveEcrDetails,

        selectedEcrRequirementsIdEncrypted,
        arrEcrRequirementOriginalFilenames,
        tblEcrRequirementsColumns,
        tblEcrManRequirements,
        tblEcrMatRequirements,
        tblEcrMachineRequirements,
        tblEcrMethodRequirements,
        tblEcrEnvironmentRequirements,
        tblEcrOthersRequirements,

        isEmptyTblEcrManRequirements,
        isEmptyTblEcrMaterialRequirements,
        isEmptyTblEcrMachineRequirements,
        isEmptyTblEcrMethodRequirements,
        isEmptyTblEcrEnvironmentRequirements,
        isEmptyTblEcrOthersRequirements,
        uploadFiles,
        getEcrRequirementRefById,
        btnLinkViewEcrRequirementRef,
        btnEcrRequirement,
    };
}
