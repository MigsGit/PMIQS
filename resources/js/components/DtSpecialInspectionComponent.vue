<template>
    <DataTable
        width="100%" cellspacing="0"
        class="table mt-2"
        ref="tblSpecialInspection"
        :columns="tblSpecialInspectionColumns"
        ajax="api/load_special_inspection_by_ecr_id"
        :options="{
            serverSide: true, //Serverside true will load the network  //ecrsId
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
                <th> Product Detail </th>
                <th> Lot Qty </th>
                <th> Samples </th>
                <th> Mod </th>
                <th> Mod Qty </th>
                <th> Judgement </th>
                <th> Inspection Date </th>
                <th> Inspector </th>
                <th> Remarks </th>
            </tr>
        </thead>
    </DataTable>
</template>

<script setup>
    import {defineProps} from 'vue'

    const props = defineProps({
        tblSpecialInspectionColumns: {
            required: true,
        },
        commonVar: {
            required: true,
        },
    })
</script>

<style lang="scss" scoped>

</style>
