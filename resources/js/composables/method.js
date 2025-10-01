import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';

export default function useMachine(){
    const methodVar = reactive({
        prdnAssessedBy : [],
        prdnCheckedBy : [],
        ppcAssessedBy : [],
        ppcCheckedBy : [],
        proEnggAssessedBy : [],
        proEnggCheckedBy : [],
        mainEnggAssessedBy : [],
        mainEnggCheckedBy : [],
        qcAssessedBy : [],
        qcCheckedBy : [],
    });
    const frmMethod = ref ({
        ecrsId : "",
        prdnAssessedBy : "",
        prdnCheckedBy : "",
        ppcAssessedBy : "",
        ppcCheckedBy : "",
        proEnggAssessedBy : "",
        proEnggCheckedBy : "",
        mainEnggAssessedBy : "",
        mainEnggCheckedBy : "",
        qcAssessedBy : "",
        qcCheckedBy : "",

    });
    return {
        methodVar,
        frmMethod,
    };
}
