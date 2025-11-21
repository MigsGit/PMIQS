import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';
import useForm from "./utils/useForm";
export default function useCommon(){
    const { axiosFetchData } = useFetch(); // Call  the useFetch function
    const  { axiosSaveData } = useForm();
    const modalCommon ={}

    //Ref State
    const rapidxUserDeptGroup = ref([]);
    //Reactive State
    const commonVar = reactive({
        isSessionApprover : false,
        isSessionPmiInternalApprover : false,
        optUserMaster:[],
        optAdminAccess : [],
        optCategoryAdminAccess : [],
        arrOptCategoryAdminAccess : [],
        rapidxUserDeptGroup : '',
        rapidxUserDeptId: '',
        isActiveTab : '',
        division : [
            {"value":"TS","label":"TS"},
            {"value":"CN","label":"CN"},
            {"value":"PPD","label":"PPD"},
            {"value":"YF","label":"YF"},
        ],
        category : [
            {"value":"","label":"-Select an option-"},
            {"value":"RM","label":"Raw Materials"},
            {"value":"PRO","label":"Product"},
        ],
        uom : [
            {"value":"","label":"-Select an Option-",selected:true,disabled:true},
            {"value":1,"label":"pcs"},
            {"value":2,"label":"roll"},
        ],
    });
    //Ref State
    const tblSpecialInspection = ref(null);
    const modalSaveSpecialInspection = ref(null);
    const modalExternalDisposition = ref(null);
    const externalDisposition  = ref(null);

    const frmSpecialInspection = ref({
        productDetail : "",
        lotQty : "",
        samples : "",
        mod : "",
        modQty : "",
        judgement : "",
        inspectionDate : "",
        inspector : "",
        lqcSectionHead : "",
        remarks : "",
    });
    //Params
    const specialInsQcInspectorParams = {
        globalVar: commonVar.optUserMaster,
        formModel: toRef(frmSpecialInspection.value,'inspector'),
        selectedVal: '',
    };
    //Params
    const specialInsLqcParams = {
        globalVar: commonVar.optUserMaster,
        formModel: toRef(frmSpecialInspection.value,'lqcSectionHead'),
        selectedVal: '',
    };
    //DT Columns
    const tblSpecialInspectionColumns = [
        {   title: '<i class="fa fa-cogs"></i>',
            data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetSpecialInspectionId = cell.querySelector('#btnGetSpecialInspectionId');
                if(btnGetSpecialInspectionId != null){
                    btnGetSpecialInspectionId.addEventListener('click',function(){
                        let specialInspectionsId = this.getAttribute('special-inspections-id');
                        getSpecialInspectionById(specialInspectionsId);
                        modal.SaveSpecialInspection.show();
                    });
                }
            }
        } ,
        {  title: "Product Detail" , data: 'product_detail'} ,
        {  title: "Lot Qty" , data: 'lot_qty'} ,
        {  title: "Samples" , data: 'samples' } ,
        {  title: "Mod" , data: 'mod' } ,
        {  title: "Mod Qty" , data: 'mod_qty' } ,
        {  title: "Judgement" , data: 'judgement' } ,
        {  title: "Inspection Date" , data: 'inspection_date' } ,
        {  title: "Inspector" , data: 'get_inspector' } ,
        {  title: "Remarks" , data: 'remarks' } ,
    ];
    //Functions

    const getAdminAccessOpt = async (category=null) => {
        let apiParams = {};
        axiosFetchData(apiParams,'api/get_admin_access_opt',function(response){
            let data = response.data;
            let userDeptGroup = data.departmentGroup;
            commonVar.rapidxUserDeptId = data.department_id;
            commonVar.rapidxUserDeptGroup = userDeptGroup;
            rapidxUserDeptGroup.value = userDeptGroup;
            // departmentGroup
            if(userDeptGroup === "ISS" ||  userDeptGroup === "QAD"){
                commonVar.optAdminAccess = [
                    {"value":"all","label":"Show All"},
                    {"value":"created","label":"Show my request"},
                ];
            }else{
                commonVar.optAdminAccess = [
                    {"value":"created","label":"Show my request"},
                ];
            }

        });
    }
    const getCategoryAdminAccessOpt = async (category=null) => {
        let apiParams = {};
        axiosFetchData(apiParams,'api/get_admin_access_opt',function(response){
            let data = response.data;
            let userDeptGroup = data.departmentGroup;
            commonVar.rapidxUserDeptId = data.department_id;
            commonVar.rapidxUserDeptGroup = userDeptGroup;
            rapidxUserDeptGroup.value = userDeptGroup;
            if(userDeptGroup === "ISS" ||  userDeptGroup === "QAD"){
                commonVar.optCategoryAdminAccess = [
                    {"value":"all","label":"Show All"},
                    {"value":"created","label":"Show my request"},
                    {"value":"pmi","label":"Pending PMI Approval"},

                ];
            }else{
                commonVar.optCategoryAdminAccess = [
                    {"value":"created","label":"Show my request"},
                    {"value":"pmi","label":"Pending PMI Approval"},
                ];
            }
        });
    }
    const getCurrentApprover = async (params) => {
        let apiParams = {
            selectedId : params.selectedId,
            approvalType : params.approvalType,
        }
        axiosFetchData(apiParams,'api/get_current_approver_session',function(response){
            let data = response.data;
            commonVar.isSessionApprover = data.isSessionApprover;

        });
    }
    const getCurrentPmiInternalApprover = async (params) => {
        let apiParams = {
            ecrsId : params.ecrsId
        }
        axiosFetchData(apiParams,'api/get_current_pmi_internal_approver',function(response){
            let data = response.data;
            commonVar.isSessionPmiInternalApprover = data.isSessionPmiInternalApprover;
        });
    }
    const saveSpecialInspection = async () => {
        let formData = new FormData();
         //Append form data
         [
            ["ecrs_id" , frmSpecialInspection.value.ecrsId],
            ["special_inspections_id" , frmSpecialInspection.value.specialInspectionsId ?? ""],
            ["product_detail" , frmSpecialInspection.value.productDetail],
            ["lot_qty" , frmSpecialInspection.value.lotQty],
            ["samples" , frmSpecialInspection.value.samples],
            ["mod" , frmSpecialInspection.value.mod],
            ["mod_qty" , frmSpecialInspection.value.modQty],
            ["judgement" , frmSpecialInspection.value.judgement],
            ["inspection_date" , frmSpecialInspection.value.inspectionDate],
            ["inspector" , frmSpecialInspection.value.inspector],
            ["lqc_section_head" , frmSpecialInspection.value.lqcSectionHead],
            ["remarks" , frmSpecialInspection.value.remarks],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_special_inspection', (response) =>{
            console.log(response);
            tblSpecialInspection.value.dt.ajax.url("api/load_special_inspection_by_ecr_id?ecrsId="+frmSpecialInspection.value.ecrsId).draw();
            modal.SaveSpecialInspection.hide();
        });
    }
    const getSpecialInspectionById = async (specialInspectionsId) => {
        let apiParams = {
            specialInspectionsId : specialInspectionsId
        }
        axiosFetchData(apiParams,'api/get_special_inspection_by_id',function(response){
            let data = response.data; //frmSpecialInspection.value.lqcSectionHead
            let specialInspection = response.data.specialInspection;
            frmSpecialInspection.value.specialInspectionsId = specialInspection.id;
            frmSpecialInspection.value.ecrsId = specialInspection.ecrs_id;
            frmSpecialInspection.value.productDetail = specialInspection.product_detail;
            frmSpecialInspection.value.lotQty = specialInspection.lot_qty;
            frmSpecialInspection.value.samples = specialInspection.samples;
            frmSpecialInspection.value.mod = specialInspection.mod;
            frmSpecialInspection.value.modQty = specialInspection.mod_qty;
            frmSpecialInspection.value.judgement = specialInspection.judgement;
            frmSpecialInspection.value.inspectionDate = specialInspection.inspection_date;
            frmSpecialInspection.value.lqcSectionHead = specialInspection.lqc_section_head;
            frmSpecialInspection.value.inspector = specialInspection.inspector;
            frmSpecialInspection.value.remarks = specialInspection.remarks;
        });
    }
    const changeExternalDisposition = async (event) => {
        externalDisposition.value =  Array.from(event.target.files);
    }
    const btnLinkViewExternalDisposition = async (selectedEcrsId,index) => {
        window.open(`api/view_external_disposition?ecrsId=${selectedEcrsId} && index=${index} && imageType=after`, '_blank');
    }
    const resetEcrForm = async (frmElement) => {
        for (const key in frmElement) {
            frmElement[key] = '';
        }
    };

    return {
        rapidxUserDeptGroup,
        modalCommon,
        commonVar,
        externalDisposition,
        tblSpecialInspection,
        tblSpecialInspectionColumns,
        modalSaveSpecialInspection,
        modalExternalDisposition,
        specialInsQcInspectorParams,
        specialInsLqcParams,
        saveSpecialInspection,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        changeExternalDisposition,
        btnLinkViewExternalDisposition,
        getAdminAccessOpt,
        getCategoryAdminAccessOpt,
        resetEcrForm,
        frmSpecialInspection,
    }

}
